<?php

/*
|--------------------------------------------------------------------------
| SEO & structured-data defaults
|--------------------------------------------------------------------------
| Central place for the brand/marketing metadata that feeds the public
| site's <head> (titles, descriptions, Open Graph, Twitter cards) and the
| JSON-LD structured data (Organization + WebSite). Everything here is
| overridable per-page from the Blade views, and the sensitive/deployment
| bits (domain, handles) come from .env so nothing is hard-coded.
|
| After editing .env, run `php artisan config:clear`.
*/

return [

    // Brand name shown in <title> suffix, og:site_name, and schema.
    'site_name' => env('SEO_SITE_NAME', 'Best CPMS'),

    // One-line positioning used for the homepage title suffix and defaults.
    'tagline' => env('SEO_TAGLINE', 'Construction Project Management Software'),

    // Fallback meta description when a page doesn't set its own.
    'default_description' => env(
        'SEO_DEFAULT_DESCRIPTION',
        'Best CPMS is construction project management software for builders and contractors — plan schedules, control budgets and BOQ, and track site progress from tender to handover, in one place.'
    ),

    // Default keyword set. Individual pages append their own focused terms.
    'default_keywords' => env(
        'SEO_DEFAULT_KEYWORDS',
        'construction project management software, construction project management system, construction management software, construction scheduling software, BOQ software, construction cost control, site progress tracking, project management for construction companies'
    ),

    // Primary market emphasis — used in copy and structured data region hints.
    // Kept broad ("IN" lead, global-friendly wording) per the build brief.
    'region' => env('SEO_REGION', 'IN'),

    // Open Graph / Twitter share image (1200x630). Served from /public.
    'og_image' => env('SEO_OG_IMAGE', '/og-image.png'),

    'locale' => env('SEO_LOCALE', 'en_IN'),

    // Twitter/X handle for twitter:site (include the leading @), or null.
    'twitter_handle' => env('SEO_TWITTER_HANDLE'),

    /*
    | Organization — powers the Organization JSON-LD block. Logo should be a
    | square-ish raster/SVG reachable at a public URL. contactPoint and
    | address are optional; fill them in via .env for richer local SEO.
    */
    'organization' => [
        'legal_name'   => env('SEO_ORG_LEGAL_NAME', 'Best CPMS'),
        'logo'         => env('SEO_ORG_LOGO', '/favicon.svg'),
        'email'        => env('SEO_ORG_EMAIL'),
        'telephone'    => env('SEO_ORG_PHONE'),
        'country'      => env('SEO_ORG_COUNTRY', 'IN'),
        'locality'     => env('SEO_ORG_LOCALITY'),
        'region'       => env('SEO_ORG_REGION'),
    ],

    /*
    | sameAs — the brand's social profiles for the Organization schema. These
    | reuse the public footer's social links (config/social.php). Only real,
    | configured URLs are emitted; platform-home fallbacks are filtered out
    | in the layout so we don't claim profiles that don't exist.
    */
    'same_as' => array_values(array_filter([
        env('SOCIAL_LINKEDIN_URL'),
        env('SOCIAL_TWITTER_URL'),
        env('SOCIAL_FACEBOOK_URL'),
        env('SOCIAL_INSTAGRAM_URL'),
    ])),

];
