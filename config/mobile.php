<?php

/*
|--------------------------------------------------------------------------
| Android field app on Google Play
|--------------------------------------------------------------------------
| The app is installed from Google Play only; the website no longer serves an
| APK. Set the public listing URL once the app is in production, or the
| internal-testing opt-in link (Play Console → Testing → Internal testing →
| Testers → "Copy link") while it is in testing.
*/

return [

    'android_package' => env('ANDROID_PACKAGE', 'com.bestcpms.cpms_mobile'),

    // https://play.google.com/store/apps/details?id=com.bestcpms.cpms_mobile
    'play_store_url' => env('PLAY_STORE_URL'),

    // https://play.google.com/apps/internaltest/...
    'play_testing_url' => env('PLAY_TESTING_URL'),

];
