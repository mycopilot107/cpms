<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Public footer social links
    |--------------------------------------------------------------------------
    |
    | URLs for the social-media icons shown in the public site footer. Set your
    | real profile URLs via the SOCIAL_*_URL variables in .env. If a variable
    | is not set, the link falls back to that platform's home page so the icon
    | still resolves somewhere valid. Set a variable to an empty value to hide
    | that icon entirely.
    |
    | NOTE: these are marketing links only. They are NOT the OAuth login
    | providers used for "Sign in with Google/Facebook" — those credentials
    | live in config/services.php.
    |
    */

    'links' => [
        'linkedin'  => env('SOCIAL_LINKEDIN_URL', 'https://www.linkedin.com'),
        'twitter'   => env('SOCIAL_TWITTER_URL', 'https://twitter.com'),
        'facebook'  => env('SOCIAL_FACEBOOK_URL', 'https://www.facebook.com'),
        'instagram' => env('SOCIAL_INSTAGRAM_URL', 'https://www.instagram.com'),
    ],

];
