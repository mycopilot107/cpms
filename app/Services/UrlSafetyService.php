<?php

namespace App\Services;

/**
 * Guards server-side fetches of third-party URLs (SSRF): only http(s) URLs
 * whose host resolves exclusively to public IP addresses are allowed.
 */
class UrlSafetyService
{
    /**
     * True only if the URL is http(s) and every IP its host resolves to is
     * publicly routable — rejects loopback, private, link-local (including the
     * 169.254.169.254 cloud metadata address), and other reserved ranges.
     */
    public function isPubliclyRoutable(string $url): bool
    {
        $parts = parse_url($url);
        if ($parts === false || empty($parts['scheme']) || empty($parts['host'])) {
            return false;
        }
        if (! in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            return false;
        }

        $host = $parts['host'];

        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            return $this->isPublicIp($host);
        }

        $ips = $this->resolveAllIps($host);
        if ($ips === []) {
            return false;
        }

        foreach ($ips as $ip) {
            if (! $this->isPublicIp($ip)) {
                return false;
            }
        }

        return true;
    }

    private function isPublicIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }

    /**
     * @return list<string>
     */
    private function resolveAllIps(string $host): array
    {
        $ips = [];

        $records = @dns_get_record($host, DNS_A + DNS_AAAA);
        if (is_array($records)) {
            foreach ($records as $record) {
                if (! empty($record['ip'])) {
                    $ips[] = $record['ip'];
                }
                if (! empty($record['ipv6'])) {
                    $ips[] = $record['ipv6'];
                }
            }
        }

        if ($ips === []) {
            $resolved = @gethostbyname($host);
            if ($resolved !== $host && filter_var($resolved, FILTER_VALIDATE_IP) !== false) {
                $ips[] = $resolved;
            }
        }

        return array_values(array_unique($ips));
    }
}
