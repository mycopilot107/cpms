@echo off
REM Build the Best CPMS field app as a release Android APK and copy it to
REM public\downloads\cpms-field-app.apk (where the web app serves downloads).
REM
REM Usage:   build_apk.bat [API_BASE_URL]
REM Example: build_apk.bat https://app.yourcompany.com/api
REM
REM With no API_BASE_URL the app uses its built-in default
REM (http://10.0.2.2:8000/api), which only works on an Android emulator.
REM For a build you hand to field staff, always pass the real URL.
REM See ..\docs\MOBILE-BUILD.md for the HTTPS / cleartext-HTTP caveat.

setlocal
cd /d "%~dp0"

set "API_BASE_URL=%~1"
set "OUT_DIR=%~dp0..\public\downloads"
set "OUT_APK=%OUT_DIR%\cpms-field-app.apk"

where flutter >nul 2>nul
if errorlevel 1 (
  echo ERROR: flutter is not installed or not on PATH.
  echo Install Flutter 3.x from https://docs.flutter.dev/get-started/install and re-run.
  exit /b 1
)

call flutter --version

if not exist "android" (
  echo ==^> No android folder yet - generating it with "flutter create ."
  call flutter create .
)

echo ==^> Fetching packages
call flutter pub get

echo ==^> Building release APK
if "%API_BASE_URL%"=="" (
  echo WARNING: no API_BASE_URL given - the app will use its built-in default
  echo          http://10.0.2.2:8000/api, which only works on an Android emulator.
  echo          Re-run with the real URL, e.g.: build_apk.bat https://app.example.com/api
  call flutter build apk --release
) else (
  echo     API base URL: %API_BASE_URL%
  call flutter build apk --release --dart-define=API_BASE_URL=%API_BASE_URL%
)

set "BUILT=build\app\outputs\flutter-apk\app-release.apk"
if not exist "%BUILT%" (
  echo ERROR: expected APK not found at %BUILT%
  exit /b 1
)

if not exist "%OUT_DIR%" mkdir "%OUT_DIR%"
copy /y "%BUILT%" "%OUT_APK%" >nul

echo.
echo Done. Copied to: public\downloads\cpms-field-app.apk
echo It is now downloadable from /get-the-app and the login page.
endlocal
