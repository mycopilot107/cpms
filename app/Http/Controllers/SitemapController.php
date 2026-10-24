<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

/**
 * Generates /sitemap.xml and /robots.txt dynamically so both always reflect
 * the current APP_URL — no hard-coded domain to forget when you deploy.
 *
 * Only the public marketing, informational and legal pages are listed.
 * Authenticated application routes (dashboard, projects, finance, API, …) are
 * deliberately excluded here and explicitly disallowed in robots.txt.
 */
class SitemapController extends Controller
{
    /**
     * Public URLs with crawl hints, keyed by route name so each <loc> always
     * matches the real registered route.
     *
     * @return array<int, array{route:string, priority:string, freq:string}>
     */
    protected function pages(): array
    {
        $pages = [
            ['route' => 'home',              'priority' => '1.0', 'freq' => 'weekly'],
            ['route' => 'cpm-software',      'priority' => '0.9', 'freq' => 'weekly'],

            // Priority keyword pages — the five phrases we want discovered first.
            ['route' => 'landing.india',            'priority' => '0.9', 'freq' => 'weekly'],
            ['route' => 'landing.project-tracking', 'priority' => '0.9', 'freq' => 'weekly'],
            ['route' => 'landing.budget',           'priority' => '0.9', 'freq' => 'weekly'],
            ['route' => 'landing.expenses',         'priority' => '0.9', 'freq' => 'weekly'],
            ['route' => 'landing.reporting',        'priority' => '0.9', 'freq' => 'weekly'],
            ['route' => 'landing.management',       'priority' => '0.9', 'freq' => 'weekly'],
            ['route' => 'landing.scheduling',       'priority' => '0.9', 'freq' => 'weekly'],
            ['route' => 'features',          'priority' => '0.8', 'freq' => 'monthly'],
            ['route' => 'pricing',           'priority' => '0.8', 'freq' => 'monthly'],
            ['route' => 'services.planning', 'priority' => '0.7', 'freq' => 'monthly'],
            ['route' => 'services.cost',     'priority' => '0.7', 'freq' => 'monthly'],
            ['route' => 'services.field',    'priority' => '0.7', 'freq' => 'monthly'],

            // SEO "Solutions" keyword landing pages (config/landing.php).
            ['route' => 'landing.contractors',     'priority' => '0.7', 'freq' => 'monthly'],
            ['route' => 'landing.builders',        'priority' => '0.7', 'freq' => 'monthly'],
            ['route' => 'landing.small-business',  'priority' => '0.7', 'freq' => 'monthly'],
            ['route' => 'landing.developers',      'priority' => '0.7', 'freq' => 'monthly'],
            ['route' => 'landing.task-management', 'priority' => '0.7', 'freq' => 'monthly'],
            ['route' => 'landing.boq',             'priority' => '0.7', 'freq' => 'monthly'],
            ['route' => 'landing.gantt',           'priority' => '0.7', 'freq' => 'monthly'],
            ['route' => 'landing.milestones',      'priority' => '0.7', 'freq' => 'monthly'],
            ['route' => 'landing.payments',        'priority' => '0.7', 'freq' => 'monthly'],

            ['route' => 'app.download-page', 'priority' => '0.6', 'freq' => 'monthly'],
            ['route' => 'reviews',           'priority' => '0.6', 'freq' => 'monthly'],
            ['route' => 'case-studies',      'priority' => '0.6', 'freq' => 'monthly'],
            ['route' => 'faq',               'priority' => '0.6', 'freq' => 'monthly'],
            ['route' => 'about',             'priority' => '0.5', 'freq' => 'monthly'],
            ['route' => 'blog',              'priority' => '0.5', 'freq' => 'weekly'],
            ['route' => 'contact',           'priority' => '0.5', 'freq' => 'yearly'],
            ['route' => 'team',              'priority' => '0.4', 'freq' => 'yearly'],
            ['route' => 'careers',           'priority' => '0.4', 'freq' => 'yearly'],
            ['route' => 'help',              'priority' => '0.4', 'freq' => 'yearly'],
            ['route' => 'mission',           'priority' => '0.3', 'freq' => 'yearly'],
            ['route' => 'feedback',          'priority' => '0.3', 'freq' => 'yearly'],
            ['route' => 'privacy',           'priority' => '0.2', 'freq' => 'yearly'],
            ['route' => 'terms',             'priority' => '0.2', 'freq' => 'yearly'],
            ['route' => 'refund',            'priority' => '0.2', 'freq' => 'yearly'],
            ['route' => 'cookies',           'priority' => '0.2', 'freq' => 'yearly'],
        ];

        foreach (array_keys(config('blog.posts', [])) as $slug) {
            $pages[] = [
                'route' => 'blog.show',
                'params' => ['slug' => $slug],
                'priority' => '0.5',
                'freq' => 'monthly',
            ];
        }

        return $pages;
    }

    /** XML sitemap of every public URL. */
    public function sitemap(): Response
    {
        $lastmod = now()->toDateString();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($this->pages() as $page) {
            if (! Route::has($page['route'])) {
                continue; // defensive: skip any route not registered
            }

            $xml .= "  <url>\n";
            $xml .= '    <loc>'.e(route($page['route'], $page['params'] ?? []))."</loc>\n";
            $xml .= '    <lastmod>'.$lastmod."</lastmod>\n";
            $xml .= '    <changefreq>'.$page['freq']."</changefreq>\n";
            $xml .= '    <priority>'.$page['priority']."</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>'."\n";

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    /** robots.txt — allow the marketing site, block the private app, point at the sitemap. */
    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            '',
            '# Private application areas — no SEO value, keep crawlers out.',
            'Disallow: /dashboard',
            'Disallow: /login',
            'Disallow: /register',
            'Disallow: /password',
            'Disallow: /forgot-password',
            'Disallow: /reset-password',
            'Disallow: /projects',
            'Disallow: /tasks',
            'Disallow: /users',
            'Disallow: /finance',
            'Disallow: /planning',
            'Disallow: /reports',
            'Disallow: /subscription',
            'Disallow: /settings',
            'Disallow: /platform',
            'Disallow: /api/',
            'Disallow: /auth/',
            '',
            '# The app landing page (/get-the-app) is public and indexable; only',
            '# keep crawlers off the raw APK binary to save crawl budget.',
            'Disallow: /download/',
            '',
            'Sitemap: '.url('/sitemap.xml'),
            '',
        ];

        return response(implode("\n", $lines), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
