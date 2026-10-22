<?php

/*
|--------------------------------------------------------------------------
| Authentication Language Lines
|--------------------------------------------------------------------------
|
| Used by the web LoginRequest and the mobile API AuthController when
| rejecting bad credentials or throttling. Without this file those calls
| surface the raw key (e.g. "auth.failed") instead of a sentence.
|
*/

return [
    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',
];
