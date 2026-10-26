#!/usr/bin/env bash
#
# Build the Best CPMS field app as a release Android APK and drop it where the web
# app serves downloads from (public/downloads/cpms-field-app.apk).
#
# Usage:
#   ./build_apk.sh [API_BASE_URL]
#
# Examples:
#   ./build_apk.sh https://app.yourcompany.com/api
#   API_BASE_URL=https://app.yourcompany.com/api ./build_apk.sh
#
# If no API base URL is given, the app uses its built-in default,
# https://bestcpms.com/api. Pass a URL only to build for another server.

set -euo pipefail

# Resolve paths relative to this script, so it works from any directory.
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

OUT_DIR="$SCRIPT_DIR/../public/downloads"
OUT_APK="$OUT_DIR/cpms-field-app.apk"
API_BASE_URL="${1:-${API_BASE_URL:-}}"

# --- prerequisites ---------------------------------------------------------
if ! command -v flutter >/dev/null 2>&1; then
  echo "ERROR: flutter is not installed or not on PATH." >&2
  echo "Install Flutter (3.x / Dart 3): https://docs.flutter.dev/get-started/install" >&2
  exit 1
fi

echo "==> Flutter: $(flutter --version | head -n 1)"

# --- first run: generate the git-ignored platform folders ------------------
if [ ! -d "android" ]; then
  echo "==> No android/ folder yet — generating it with 'flutter create .'"
  flutter create .
fi

echo "==> Fetching packages"
flutter pub get

# --- build -----------------------------------------------------------------
echo "==> Building release APK"
if [ -n "$API_BASE_URL" ]; then
  echo "    API base URL: $API_BASE_URL"
  flutter build apk --release --dart-define=API_BASE_URL="$API_BASE_URL"
else
  echo "    API base URL: https://bestcpms.com/api (built-in default)"
  flutter build apk --release
fi

BUILT="build/app/outputs/flutter-apk/app-release.apk"
if [ ! -f "$BUILT" ]; then
  echo "ERROR: expected APK not found at $BUILT" >&2
  exit 1
fi

mkdir -p "$OUT_DIR"
cp "$BUILT" "$OUT_APK"

echo
echo "✓ Done. Copied to: public/downloads/cpms-field-app.apk"
echo "  It is now downloadable from /get-the-app and the login page."
