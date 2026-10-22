# public/downloads

The Best CPMS web app serves the **field app build** from this folder.

## Expected file

```
cpms-field-app.apk
```

The download link on the login page and the `/get-the-app` landing page look
for exactly this filename. When it is present, visitors can download it; when
it is absent, the pages show a friendly "coming soon" message instead (nothing
404s).

## How it gets here

The APK is **not** committed to the repository (it is a large binary that
changes every build — see `.gitignore`). Produce it from the Flutter project
and drop it here:

```sh
cd mobile
./build_apk.sh https://your-real-domain.com/api     # macOS / Linux
#  or, on Windows:
build_apk.bat https://your-real-domain.com/api
```

The script builds a release APK and copies it to
`public/downloads/cpms-field-app.apk` for you. See `docs/MOBILE-BUILD.md` for
the full walkthrough, prerequisites, and the HTTPS/cleartext gotcha.

## Deploying

Because the APK is git-ignored, remember to upload it to this path on your
server separately (or run the build there). Anything you place in this folder
is publicly downloadable.
