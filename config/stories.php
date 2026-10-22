<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Success stories — moderation
    |--------------------------------------------------------------------------
    |
    | Signed-in users can submit a success story from inside the app. Nothing
    | they submit appears on the public site until a site administrator
    | approves it here.
    |
    | `admin_email` is the single platform-level moderator. The app has no
    | separate "super admin" account (every role is company-scoped), so the
    | moderation screen is gated to whichever account matches this email. Set
    | it in .env; leave blank to lock the screen to nobody (safe default).
    |
    */

    'admin_email' => env('STORIES_ADMIN_EMAIL'),

    /*
    | How many approved stories to show in the homepage spotlight rotation.
    | The reviews page lists all approved stories.
    */
    'home_limit' => 1,

];
