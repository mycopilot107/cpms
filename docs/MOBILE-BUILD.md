# Building & distributing the Best CPMS field app

The Android app is distributed two ways, both shown on `/get-the-app`:

- **Google Play** (internal testing first, production later): about 9 MB per
  phone, updates automatically, nothing to allow in phone settings.
- **Direct APK** (about 16 MB) for phones without Play access. Build it with
  `./build_apk.sh`; it lands in `storage/app/downloads/best-cpms-field.apk`
  (outside `public/`) and is streamed by `/download/android`. That one file is
  tracked by git, so commit and push it with the code — the button appears once
  the file exists on the server.

An APK install and a Play install are signed differently (Play re-signs with
Google's key), so they can't update each other; switching needs an uninstall.

For running the app on an emulator during development, see
[`docs/MOBILE.md`](MOBILE.md).

## Build the bundle

```sh
cd mobile
./build_play_bundle.sh                         # uses https://bestcpms.com/api
./build_play_bundle.sh https://staging.example.com/api   # another server
```

Output: `mobile/build/app/outputs/bundle/release/app-release.aab`.

Before every upload, raise the build number in `mobile/pubspec.yaml`
(`version: 1.1.0+2` → `1.1.1+3`). Play refuses a version code it has already seen.

## Signing (upload key)

Play only accepts bundles signed with your **upload key**.
`mobile/android/key.properties` (git-ignored) points at the keystore:

```properties
storePassword=…
keyPassword=…
keyAlias=upload
storeFile=/home/hp/.android-keys/bestcpms-upload.jks
```

- Keep the keystore **and** its password backed up somewhere safe (password
  manager plus an offline copy). Never commit them or put them under `public/`.
- With **Play App Signing** (the default for new apps) Google holds the real app
  signing key. If the upload key is ever lost, it can be reset from Play
  Console → Setup → App signing, but that takes a support request — backups are
  much easier.
- Without `key.properties`, release builds fall back to the debug key; Play will
  reject those.

## First upload — internal testing

1. Create a Google Play developer account (one-time fee) at
   <https://play.google.com/console>.
2. **Create app** → name *Best CPMS Field*, App, Free.
3. **Testing → Internal testing → Testers**: create an email list with the
   Google accounts of your testers (up to 100), save, and **Copy link** — this
   is the opt-in URL testers open on their phones.
4. **Create new release** → upload `app-release.aab` → add release notes →
   **Review release** → **Start rollout to Internal testing**.
5. Fill the required **App content** pages:
   - Privacy policy: `https://bestcpms.com/privacy-policy`
   - Data safety: the app collects name, email, and photos the user takes;
     data is sent over HTTPS and users can request deletion.
   - Ads: none. Target audience: 18+. Content rating questionnaire.
6. On the server `.env` set
   `PLAY_TESTING_URL=<the copied opt-in link>`, then `php artisan config:clear`.
   `/get-the-app` now shows **Join the test on Google Play**.

Testers open the link on their phone with the listed Google account, tap
**Become a tester**, then install from the Play Store.

## Going public later

Promote the release to **Production** in Play Console (Google reviews it,
usually within a few days). Then set
`PLAY_STORE_URL=https://play.google.com/store/apps/details?id=com.bestcpms.cpms_mobile`
— it takes priority over the testing link, and the page shows **Get it on
Google Play**.

The application id `com.bestcpms.cpms_mobile` is permanent once the first
bundle is uploaded.

## How the website links to it

| Piece | Where |
|-------|-------|
| Play links (`PLAY_STORE_URL`, `PLAY_TESTING_URL`) | `config/mobile.php` |
| Landing page with the Google Play button | `GET /get-the-app` → `resources/views/public/download-app.blade.php` |
| APK download | `GET /download/android` streams the APK (redirects to Google Play when no APK is deployed) |
| "Get the Android app" row on login/register | `resources/views/auth/partials/app-download.blade.php` |
| Controller | `app/Http/Controllers/DownloadController.php` |

## Point the build at your server

The API URL is a compile-time constant (`lib/config.dart`, default
`https://bestcpms.com/api`). Pass another one to the build script only for a
staging server. The app only talks HTTPS.

iOS needs a Mac with Xcode and an Apple Developer account (`flutter build ipa`);
until then iPhone users can use the responsive web app.
