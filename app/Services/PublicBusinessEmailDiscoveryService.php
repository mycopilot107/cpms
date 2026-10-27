<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Finds publicly published business/contact emails for a search query, such
 * as "construction contractors indore". It searches the web, visits the
 * matching business sites (respecting robots.txt), and returns only emails
 * that actually appear on those public pages — it never guesses addresses.
 */
class PublicBusinessEmailDiscoveryService
{
    public const EMAILS_PER_REQUEST = 50;

    private const BOT_UA = 'Mozilla/5.0 (compatible; BestCPMSBot/1.0; +https://bestcpms.com)';

    private const MAX_PAGES_PER_HOST = 2;

    private const PAGE_TIMEOUT = 8;

    /** @var list<string> */
    private const SKIP_HOST_SUFFIXES = [
        'google.com', 'google.co.in', 'googleapis.com', 'gstatic.com',
        'bing.com', 'duckduckgo.com', 'yahoo.com', 'yandex.com', 'baidu.com',
        'facebook.com', 'instagram.com', 'twitter.com', 'x.com', 'linkedin.com',
        'youtube.com', 'youtu.be', 'reddit.com', 'wikipedia.org', 'wikimedia.org',
        'apple.com', 'pinterest.com', 'tiktok.com', 'quora.com',
        'cloudflare.com', 'wix.com', 'wordpress.com', 'blogger.com',
        'justdial.com', 'indiamart.com', 'sulekha.com', 'tradeindia.com',
        'magicbricks.com', '99acres.com', 'housing.com', 'nobroker.in',
    ];

    /** @var list<string> */
    private const SKIP_EMAIL_DOMAINS = [
        'example.com', 'example.org', 'example.net', 'test.com', 'domain.com',
        'email.com', 'yourdomain.com', 'sentry.io', 'wixpress.com',
        'cloudflare.com', 'schema.org', 'w3.org', 'github.com',
        'googleapis.com', 'gstatic.com', 'bestcpms.com',
    ];

    /** @var list<string> */
    private const SKIP_LOCAL_PARTS = [
        'noreply', 'no-reply', 'donotreply', 'do-not-reply', 'mailer-daemon',
        'postmaster', 'abuse', 'webmaster', 'privacy', 'legal',
    ];

    /** @var list<string> */
    private const SKIP_PATH_FRAGMENTS = [
        '/login', '/signin', '/sign-in', '/signup', '/sign-up', '/account',
        '/wp-login', '/wp-admin', '/checkout', '/cart', '/auth/',
    ];

    public function __construct(private readonly UrlSafetyService $urlSafety) {}

    /**
     * @return list<string> Unique, validated emails only (max EMAILS_PER_REQUEST).
     */
    public function discover(string $query, int $limit = self::EMAILS_PER_REQUEST): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }

        $limit = max(1, min($limit, self::EMAILS_PER_REQUEST));
        $emails = [];
        $seenHosts = [];

        $sources = [
            fn () => $this->searchDuckDuckGo($query),
            fn () => $this->searchDuckDuckGo($query.' contact email'),
            fn () => $this->searchGoogleCse($query),
            fn () => $this->searchGoogleCse($query.' contact'),
            fn () => $this->searchOpenAiWeb($query),
            fn () => $this->searchOpenAiWeb($query.' company contact email'),
            fn () => $this->searchOpenAiWeb('top '.$query.' firms official website'),
        ];

        $resultHosts = [];
        foreach ($sources as $source) {
            if (count($emails) >= $limit) {
                break;
            }
            $urls = $source();
            foreach ($urls as $url) {
                $resultHosts[$this->hostOf((string) $this->normalizeHttpUrl($url))] = true;
            }
            $this->harvestFromUrls($urls, $emails, $seenHosts, $limit);
        }

        Log::info('Email discovery finished', [
            'query' => $query,
            'result_sites' => array_keys($resultHosts),
            'sites_checked' => count($seenHosts),
            'emails' => count($emails),
        ]);

        return array_slice(array_values(array_unique($emails)), 0, $limit);
    }

    /**
     * @return list<string>
     */
    public function extractEmailsFromHtml(string $html): array
    {
        $decoded = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $found = [];

        if (preg_match_all('/mailto:([a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,})/i', $decoded, $matches)) {
            array_push($found, ...$matches[1]);
        }

        if (preg_match_all('/data-cfemail="([a-f0-9]+)"/i', $html, $matches)) {
            foreach ($matches[1] as $encoded) {
                $cfEmail = $this->decodeCfEmail($encoded);
                if ($cfEmail !== null) {
                    $found[] = $cfEmail;
                }
            }
        }

        if (preg_match_all('/"email"\s*:\s*"([^"]+)"/i', $decoded, $matches)) {
            array_push($found, ...$matches[1]);
        }

        if (preg_match_all('/[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}/i', $decoded, $matches)) {
            array_push($found, ...$matches[0]);
        }

        $unique = [];
        foreach ($found as $raw) {
            $email = $this->normalizeFoundEmail($raw);
            if ($email !== null) {
                $unique[$email] = $email;
            }
        }

        return array_values($unique);
    }

    /**
     * @param  list<string>  $urls
     * @param  list<string>  $emails
     * @param  array<string, true>  $seenHosts
     */
    private function harvestFromUrls(array $urls, array &$emails, array &$seenHosts, int $limit): void
    {
        foreach ($urls as $url) {
            if (count($emails) >= $limit) {
                return;
            }

            $normalized = $this->normalizeHttpUrl($url);
            if ($normalized === null || $this->shouldSkipUrl($normalized)) {
                continue;
            }
            if (! $this->urlSafety->isPubliclyRoutable($normalized)) {
                continue;
            }

            $host = $this->hostOf($normalized);
            if ($host === '' || isset($seenHosts[$host])) {
                continue;
            }
            $seenHosts[$host] = true;

            $origin = $this->originOf($normalized);
            if ($origin === null) {
                continue;
            }

            $disallow = $this->fetchRobotsDisallow($origin);
            $html = $this->fetchPublicHtml($normalized, $disallow);
            if ($html === null) {
                continue;
            }

            $this->collectEmails($emails, $html, $limit);

            $pagesFetched = 1;
            foreach ($this->contactUrlsFromHtml($normalized, $html) as $contactUrl) {
                if (count($emails) >= $limit || $pagesFetched >= self::MAX_PAGES_PER_HOST) {
                    break;
                }
                $contactHtml = $this->fetchPublicHtml($contactUrl, $disallow);
                if ($contactHtml === null) {
                    continue;
                }
                $pagesFetched++;
                $this->collectEmails($emails, $contactHtml, $limit);
            }
        }
    }

    /**
     * @return list<string>
     */
    private function searchDuckDuckGo(string $query): array
    {
        try {
            $response = Http::asForm()
                ->timeout(20)
                ->connectTimeout(8)
                ->withHeaders(['User-Agent' => self::BOT_UA, 'Accept' => 'text/html'])
                ->post('https://html.duckduckgo.com/html/', ['q' => $query]);
        } catch (\Throwable $e) {
            Log::debug('Email discovery DuckDuckGo failed', ['message' => $e->getMessage()]);

            return [];
        }

        if (! $response->successful() || $this->looksLikeAccessWall($response->body()) || str_contains($response->body(), 'anomaly-modal')) {
            Log::info('Email discovery DuckDuckGo returned no usable results', ['status' => $response->status()]);

            return [];
        }

        $html = $response->body();
        $urls = [];

        if (preg_match_all('/class="result__a"[^>]*href="([^"]+)"/i', $html, $matches)) {
            foreach ($matches[1] as $href) {
                $urls[] = html_entity_decode($href, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
        }
        if (preg_match_all('/uddg=([^&"]+)/i', $html, $matches)) {
            foreach ($matches[1] as $encoded) {
                $urls[] = urldecode($encoded);
            }
        }

        return $urls;
    }

    /**
     * @return list<string>
     */
    private function searchGoogleCse(string $query): array
    {
        $key = (string) config('services.google_cse.key');
        $cx = (string) config('services.google_cse.cx');
        if ($key === '' || $cx === '') {
            return [];
        }

        try {
            $response = Http::timeout(20)->get('https://www.googleapis.com/customsearch/v1', [
                'key' => $key,
                'cx' => $cx,
                'q' => $query,
                'num' => 8,
            ]);
        } catch (\Throwable $e) {
            Log::debug('Email discovery Google CSE failed', ['message' => $e->getMessage()]);

            return [];
        }

        if (! $response->successful()) {
            return [];
        }

        return collect($response->json('items') ?? [])
            ->pluck('link')
            ->filter()
            ->map(fn ($link) => (string) $link)
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    private function searchOpenAiWeb(string $query): array
    {
        $apiKey = (string) config('services.openai.key');
        if ($apiKey === '') {
            return [];
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(45)
                ->post('https://api.openai.com/v1/responses', [
                    'model' => 'gpt-4o-mini',
                    'tools' => [['type' => 'web_search']],
                    'tool_choice' => ['type' => 'web_search'],
                    'input' => 'Search the web for businesses matching: "'.$query.'". '
                        .'List up to 15 of their own official company websites, one full URL per line. '
                        .'Do not list Google Maps, directories (Justdial, IndiaMART, Sulekha), or social media pages.',
                ]);
        } catch (\Throwable $e) {
            Log::debug('Email discovery OpenAI search failed', ['message' => $e->getMessage()]);

            return [];
        }

        if (! $response->successful()) {
            Log::warning('Email discovery OpenAI search rejected', [
                'status' => $response->status(),
                'error' => mb_substr((string) $response->json('error.message'), 0, 200),
            ]);

            return [];
        }

        $urls = [];
        foreach ($response->json('output') ?? [] as $item) {
            if (($item['type'] ?? '') !== 'message') {
                continue;
            }
            foreach ($item['content'] ?? [] as $content) {
                foreach ($content['annotations'] ?? [] as $annotation) {
                    if (($annotation['type'] ?? '') === 'url_citation' && ! empty($annotation['url'])) {
                        $urls[] = (string) $annotation['url'];
                    }
                }
                if (preg_match_all('#https?://[^\s<>"\'\)\]]+#i', (string) ($content['text'] ?? ''), $matches)) {
                    array_push($urls, ...$matches[0]);
                }
            }
        }

        return $urls;
    }

    /**
     * @param  list<string>  $disallow
     */
    private function fetchPublicHtml(string $url, array $disallow): ?string
    {
        if ($this->shouldSkipUrl($url) || $this->isDisallowed($url, $disallow)) {
            return null;
        }
        if (! $this->urlSafety->isPubliclyRoutable($url)) {
            return null;
        }

        try {
            $response = Http::timeout(self::PAGE_TIMEOUT)
                ->connectTimeout(5)
                ->withHeaders(['User-Agent' => self::BOT_UA, 'Accept' => 'text/html,application/xhtml+xml'])
                ->withOptions(['allow_redirects' => ['max' => 4]])
                ->get($url);
        } catch (\Throwable $e) {
            Log::debug('Email discovery fetch failed', ['url' => $url, 'message' => $e->getMessage()]);

            return null;
        }

        if ($response->status() >= 400) {
            return null;
        }

        $finalUrl = (string) $response->effectiveUri();
        if ($finalUrl !== '' && ($this->shouldSkipUrl($finalUrl) || $this->isDisallowed($finalUrl, $disallow))) {
            return null;
        }

        $contentType = strtolower((string) $response->header('Content-Type'));
        if ($contentType !== '' && ! str_contains($contentType, 'html') && ! str_contains($contentType, 'xml')) {
            return null;
        }

        $html = $response->body();

        return ($html === '' || $this->looksLikeAccessWall($html)) ? null : $html;
    }

    /**
     * @param  list<string>  $emails
     */
    private function collectEmails(array &$emails, string $html, int $limit): void
    {
        foreach ($this->extractEmailsFromHtml($html) as $email) {
            if (count($emails) >= $limit) {
                return;
            }
            if (! in_array($email, $emails, true)) {
                $emails[] = $email;
            }
        }
    }

    private function normalizeFoundEmail(string $raw): ?string
    {
        $email = rtrim(strtolower(trim($raw)), '.,;:>)"\'');
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        [$local, $domain] = explode('@', $email, 2);
        if (in_array($local, self::SKIP_LOCAL_PARTS, true)) {
            return null;
        }
        if (preg_match('/\.(png|jpe?g|gif|webp|svg|css|js)$/i', $local) || preg_match('/\.(png|jpe?g|gif|webp|svg)$/i', $domain)) {
            return null;
        }
        foreach (self::SKIP_EMAIL_DOMAINS as $blocked) {
            if ($domain === $blocked || str_ends_with($domain, '.'.$blocked)) {
                return null;
            }
        }

        return $email;
    }

    private function decodeCfEmail(string $encoded): ?string
    {
        $hex = @hex2bin($encoded);
        if ($hex === false || strlen($hex) < 2) {
            return null;
        }

        $key = ord($hex[0]);
        $out = '';
        for ($i = 1, $len = strlen($hex); $i < $len; $i++) {
            $out .= chr(ord($hex[$i]) ^ $key);
        }

        return $this->normalizeFoundEmail($out);
    }

    /**
     * @return list<string>
     */
    private function contactUrlsFromHtml(string $baseUrl, string $html): array
    {
        $urls = [];
        if (preg_match_all('/<a\s[^>]*href=["\']([^"\']+)["\'][^>]*>/i', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $absolute = $this->absolutizeUrl($baseUrl, html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                if ($absolute === null) {
                    continue;
                }
                $path = strtolower((string) (parse_url($absolute, PHP_URL_PATH) ?? ''));
                $isContact = str_contains($path, 'contact') || str_contains($path, 'get-in-touch') || str_contains($path, 'reach-us');
                if (! $isContact || $this->shouldSkipUrl($absolute) || $this->hostOf($absolute) !== $this->hostOf($baseUrl)) {
                    continue;
                }
                $urls[$absolute] = $absolute;
                if (count($urls) >= 2) {
                    break;
                }
            }
        }

        return array_values($urls);
    }

    private function looksLikeAccessWall(string $html): bool
    {
        $sample = strtolower(substr($html, 0, 4000));

        return str_contains($sample, 'cf-challenge')
            || str_contains($sample, 'just a moment...')
            || str_contains($sample, 'access denied')
            || (str_contains($sample, 'please enable cookies') && str_contains($sample, 'cloudflare'))
            || (strlen($html) < 4000 && (str_contains($sample, 'verify you are human') || str_contains($sample, 'unusual traffic')));
    }

    private function shouldSkipUrl(string $url): bool
    {
        $host = $this->hostOf($url);
        if ($host === '') {
            return true;
        }
        foreach (self::SKIP_HOST_SUFFIXES as $suffix) {
            if ($host === $suffix || str_ends_with($host, '.'.$suffix)) {
                return true;
            }
        }

        $path = strtolower((string) (parse_url($url, PHP_URL_PATH) ?? '/'));
        foreach (self::SKIP_PATH_FRAGMENTS as $fragment) {
            if (str_contains($path, $fragment)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private function fetchRobotsDisallow(string $origin): array
    {
        $robotsUrl = $origin.'/robots.txt';
        if (! $this->urlSafety->isPubliclyRoutable($robotsUrl)) {
            return [];
        }

        try {
            $response = Http::withHeaders(['User-Agent' => self::BOT_UA])->timeout(6)->get($robotsUrl);
        } catch (\Throwable $e) {
            return [];
        }

        if (! $response->successful()) {
            return [];
        }

        $disallow = [];
        $applies = false;

        foreach (preg_split('/\r\n|\r|\n/', $response->body()) ?: [] as $rawLine) {
            $line = trim(preg_replace('/#.*/', '', $rawLine) ?? '');
            if ($line === '' || ! str_contains($line, ':')) {
                continue;
            }
            [$field, $value] = array_map('trim', explode(':', $line, 2));
            $field = strtolower($field);
            if ($field === 'user-agent') {
                $ua = strtolower($value);
                $applies = $ua === '*' || str_contains($ua, 'bestcpmsbot');

                continue;
            }
            if ($field === 'disallow' && $applies && $value !== '') {
                $disallow[] = $value;
            }
        }

        return $disallow;
    }

    /**
     * @param  list<string>  $disallowPrefixes
     */
    private function isDisallowed(string $url, array $disallowPrefixes): bool
    {
        $path = parse_url($url, PHP_URL_PATH) ?? '/';
        foreach ($disallowPrefixes as $prefix) {
            if ($prefix === '/' || str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }

    private function normalizeHttpUrl(string $url): ?string
    {
        $url = trim(html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if (str_starts_with($url, '//')) {
            $url = 'https:'.$url;
        }
        $parts = parse_url($url);
        if ($parts === false || empty($parts['scheme']) || empty($parts['host'])) {
            return null;
        }
        $scheme = strtolower($parts['scheme']);
        if (! in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        $port = isset($parts['port']) ? ':'.$parts['port'] : '';
        $query = isset($parts['query']) ? '?'.$parts['query'] : '';

        return $scheme.'://'.strtolower($parts['host']).$port.($parts['path'] ?? '/').$query;
    }

    private function absolutizeUrl(string $base, string $href): ?string
    {
        $href = trim($href);
        $lower = strtolower($href);
        if ($href === '' || str_starts_with($href, '#') || str_starts_with($lower, 'javascript:') || str_starts_with($lower, 'mailto:')) {
            return null;
        }
        if (preg_match('#^https?://#i', $href) || str_starts_with($href, '//')) {
            return $this->normalizeHttpUrl($href);
        }

        $baseParts = parse_url($base);
        if ($baseParts === false || empty($baseParts['scheme']) || empty($baseParts['host'])) {
            return null;
        }
        $origin = $baseParts['scheme'].'://'.$baseParts['host'].(isset($baseParts['port']) ? ':'.$baseParts['port'] : '');
        if (str_starts_with($href, '/')) {
            return $this->normalizeHttpUrl($origin.$href);
        }

        $dir = preg_replace('#/[^/]*$#', '/', $baseParts['path'] ?? '/') ?? '/';

        return $this->normalizeHttpUrl($origin.$dir.$href);
    }

    private function hostOf(string $url): string
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return preg_replace('/^www\./', '', $host) ?? $host;
    }

    private function originOf(string $url): ?string
    {
        $parts = parse_url($url);
        if ($parts === false || empty($parts['scheme']) || empty($parts['host'])) {
            return null;
        }

        return $parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '');
    }
}
