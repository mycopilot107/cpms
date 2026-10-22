# Building & distributing the Best CPMS field app

How to turn the Flutter source in [`/mobile`](../mobile) into an installable
Android **APK** that field staff download straight from the website — no app
store required.

If you only want to *run* the app on an emulator in development, use
[`docs/MOBILE.md`](MOBILE.md) instead. This page is about producing a build you
hand to real phones.

## TL;DR — one command

```sh
cd mobile
./build_apk.sh https://your-real-domain.com/api      # macOS / Linux
```

```bat
cd mobile
build_apk.bat https://your-real-domain.com/api        REM Windows
```

That produces `public/downloads/cpms-field-app.apk`, which is immediately
downloadable from the **login page** ("Download the mobile app") and the
**/get-the-app** landing page.

> You cannot build an APK on a plain web server or in CI without the Android
> SDK installed. Build on a developer machine (or a CI runner) that has Flutter
> **and** the Android toolchain set up.

## Prerequisites

- **Flutter 3.x (Dart 3)** — check with `flutter --version`.
- **Android toolchain** — the Android SDK and a JDK, with
  `flutter doctor` showing Android as ready and
  `flutter doctor --android-licenses` accepted.

Run `flutter doctor` once and resolve anything it flags under "Android toolchain".

## What the script does

1. Runs `flutter create .` if the `android/` folder is missing. The platform
   folders are git-ignored, so this is expected on a fresh checkout.
2. `flutter pub get`.
3. `flutter build apk --release`, passing your API URL as a compile-time
   `--dart-define=API_BASE_URL=...` when you provide one.
4. Copies `build/app/outputs/flutter-apk/app-release.apk` to
   `public/downloads/cpms-field-app.apk`.

You can of course run those steps by hand; the script just wires them together
and drops the result in the right place.

## Point the build at your server (important)

The app reads its API base URL from a **compile-time** constant
(`String.fromEnvironment('API_BASE_URL')` in `lib/config.dart`). Whatever you
pass to the build is baked into that APK:

```sh
./build_apk.sh https://app.yourcompany.com/api
```

If you omit it, the app falls back to its built-in default
`http://10.0.2.2:8000/api` — the Android **emulator**'s alias for your PC's
localhost. That address means nothing on a real phone, so a build made without
the real URL will install fine but never connect. Always pass the real,
device-reachable URL for a build you distribute.

## The HTTPS / cleartext-HTTP gotcha (read this)

Release Android builds **block plain `http://` traffic by default** (any app
targeting Android 9 / API 28+). This trips people up because the emulator (a
debug build) allows it, so "it worked in testing" and then the released APK
can't reach the server.

- **Recommended:** serve the API over **HTTPS** and pass an `https://…` URL.
  Nothing else to do.
- **If you must use plain HTTP** (e.g. an internal LAN/VPN server with no
  certificate), opt in explicitly. After `flutter create .`, add
  `android/app/src/main/res/xml/network_security_config.xml`:

  ```xml
  <?xml version="1.0" encoding="utf-8"?>
  <network-security-config>
      <!-- Allow cleartext ONLY for your specific host. Prefer HTTPS in production. -->
      <domain-config cleartextTrafficPermitted="true">
          <domain includeSubdomains="true">192.168.1.20</domain>
      </domain-config>
  </network-security-config>
  ```

  and reference it on the `<application>` tag in
  `android/app/src/main/AndroidManifest.xml`:

  ```xml
  <application
      android:networkSecurityConfig="@xml/network_security_config"
      ... >
  ```

  **Symptom if you skip this:** on a real phone, login spins and then fails with
  a connection/handshake error, even though the emulator worked.

> `flutter create .` regenerates the `android/` folder. Because that folder is
> git-ignored, keep a copy of any files you hand-edit here (the network-security
> config, signing config, app icons) so you can re-apply them, or commit just
> those specific files.

## Signing (optional)

`flutter build apk --release` with no signing configuration signs the APK with
Flutter's debug key and prints a warning. That APK still installs fine by
sideloading, so you can ship without any signing setup.

For a cleaner update story (so Android recognises new versions as updates to the
same app) set up a real release keystore and `android/key.properties` per the
[Flutter signing guide](https://docs.flutter.dev/deployment/android#signing-the-app).
Keep the keystore file and its passwords **out of git**.

## Installing on a phone (sideload)

1. On the phone, open the website and go to **Download the mobile app** on the
   login page (or `/get-the-app`), then tap **Download for Android**.
2. Open the downloaded file. Android will ask to **allow installs** from your
   browser or Files app — allow it, then confirm the install.
3. Open **Best CPMS Field** and sign in with the same email and password used on the
   web app.

Requires Android 6.0 or newer, and the phone must be able to reach the Best CPMS
server (same network/VPN for an internal server).

## How the website serves it

| Piece | Where |
|-------|-------|
| The APK file (git-ignored; deploy separately) | `public/downloads/cpms-field-app.apk` |
| Streams the APK (apk mime + attachment); friendly notice if absent | route `app.download` → `GET /download/android` |
| Landing page with the button; shows "coming soon" until the file exists | route `app.download-page` → `GET /get-the-app` → `resources/views/public/download-app.blade.php` |
| "Download the mobile app" row on the auth pages | `resources/views/auth/partials/app-download.blade.php` (included on login + register) |
| Controller | `app/Http/Controllers/DownloadController.php` |

`robots.txt` keeps crawlers off the raw `/download/` binary but lists
`/get-the-app` in the sitemap so the landing page can be indexed.

Because the APK is git-ignored, remember to upload it to
`public/downloads/cpms-field-app.apk` on your server as part of deploying (or
run the build there).

## App-store distribution (later)

Direct APK download is the fastest way to get the app onto field phones. If you
later want the Play Store, build an **app bundle** instead and upload the `.aab`
to the Play Console:

```sh
flutter build appbundle --release --dart-define=API_BASE_URL=https://app.yourcompany.com/api
```

iOS requires a Mac with Xcode and an Apple Developer account
(`flutter build ipa`); until then, iPhone users can use the responsive web app.

## Files in this layer

```
mobile/build_apk.sh                                  # one-command build (macOS/Linux)
mobile/build_apk.bat                                 # one-command build (Windows)
app/Http/Controllers/DownloadController.php          # serves the landing page + APK
routes/web.php                                       # +get-the-app, +download/android
resources/views/public/download-app.blade.php        # download landing page
resources/views/auth/partials/app-download.blade.php # "Download the mobile app" row
public/downloads/                                    # where the APK is served from (.gitignore + README)
```
