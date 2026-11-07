<?php

/*
|--------------------------------------------------------------------------
| Android field app
|--------------------------------------------------------------------------
| Two ways to install, both shown on /get-the-app:
|  - Google Play: the public listing URL once the app is in production, or the
|    internal-testing opt-in link (Play Console → Testing → Internal testing →
|    Testers → "Copy link") while it is in testing.
|  - Direct APK: built with mobile/build_apk.sh into storage/app/private and
|    streamed by /download/android. The button only shows when the file exists.
*/

return [

    'android_package' => env('ANDROID_PACKAGE', 'com.bestcpms.cpms_mobile'),

    // https://play.google.com/store/apps/details?id=com.bestcpms.cpms_mobile
    'play_store_url' => env('PLAY_STORE_URL'),

    // https://play.google.com/apps/internaltest/...
    'play_testing_url' => env('PLAY_TESTING_URL'),

    'apk_path' => env('ANDROID_APK_PATH', storage_path('app/private/best-cpms-field.apk')),

    'apk_download_name' => 'BestCPMS-Field.apk',

];
