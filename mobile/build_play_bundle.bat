@echo off
REM Build the Best CPMS field app as a signed Android App Bundle (.aab) for
REM Google Play. Upload it in Play Console - Testing - Internal testing.
REM
REM Usage:   build_play_bundle.bat [API_BASE_URL]
REM With no API_BASE_URL the app uses its built-in default,
REM https://bestcpms.com/api.
REM
REM android\key.properties must point at the upload keystore (see
REM build_play_bundle.sh). Raise the build number in pubspec.yaml before
REM each upload.

setlocal
cd /d "%~dp0"

set "API_BASE_URL=%~1"

where flutter >nul 2>nul
if errorlevel 1 (
  echo ERROR: flutter is not installed or not on PATH.
  exit /b 1
)

if not exist "android\key.properties" (
  echo ERROR: android\key.properties is missing, so the bundle can't be signed for Google Play.
  exit /b 1
)

call flutter pub get

if "%API_BASE_URL%"=="" (
  echo     API base URL: https://bestcpms.com/api ^(built-in default^)
  call flutter build appbundle --release
) else (
  echo     API base URL: %API_BASE_URL%
  call flutter build appbundle --release --dart-define=API_BASE_URL=%API_BASE_URL%
)

set "BUILT=build\app\outputs\bundle\release\app-release.aab"
if not exist "%BUILT%" (
  echo ERROR: expected bundle not found at %BUILT%
  exit /b 1
)

echo.
echo Done: mobile\%BUILT%
echo Upload it in Play Console - Testing - Internal testing - Create new release.
endlocal
