#!/usr/bin/env bash
#
# Build the Best CPMS field app as a signed APK for direct download from
# /get-the-app (alongside Google Play). The APK is copied to
# storage/app/private/best-cpms-field.apk, outside the public folder; the
# website streams it from /download/android.
#
# Usage:
#   ./build_apk.sh [API_BASE_URL]
#
# Only ARM builds are included (every Android phone); x86_64 is emulator-only.
# Signing uses the same upload key as build_play_bundle.sh (android/key.properties).
#
# Note: an APK installed from the website and the Play Store version are signed
# differently (Play re-signs with Google's app signing key), so switching
# between the two needs an uninstall first.

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

API_BASE_URL="${1:-${API_BASE_URL:-}}"
TARGET="../storage/app/private/best-cpms-field.apk"

if ! command -v flutter >/dev/null 2>&1; then
  echo "ERROR: flutter is not installed or not on PATH." >&2
  exit 1
fi

if [ ! -f "android/key.properties" ]; then
  echo "ERROR: android/key.properties is missing, so the APK can't be signed." >&2
  exit 1
fi

echo "==> Fetching packages"
flutter pub get

echo "==> Building signed release APK"
BUILD_ARGS=(--release --target-platform android-arm,android-arm64)
if [ -n "$API_BASE_URL" ]; then
  echo "    API base URL: $API_BASE_URL"
  BUILD_ARGS+=(--dart-define=API_BASE_URL="$API_BASE_URL")
else
  echo "    API base URL: https://bestcpms.com/api (built-in default)"
fi
flutter build apk "${BUILD_ARGS[@]}"

BUILT="build/app/outputs/flutter-apk/app-release.apk"
if [ ! -f "$BUILT" ]; then
  echo "ERROR: expected APK not found at $BUILT" >&2
  exit 1
fi

mkdir -p "$(dirname "$TARGET")"
cp "$BUILT" "$TARGET"

echo
echo "✓ Done: storage/app/private/best-cpms-field.apk ($(du -h "$TARGET" | cut -f1))"
echo "  Commit and push it; /get-the-app shows the APK button once the file is on the server."
