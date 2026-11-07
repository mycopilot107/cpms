#!/usr/bin/env bash
#
# Build the Best CPMS field app as a signed Android App Bundle (.aab) for
# Google Play. Upload the result in Play Console → Testing → Internal testing
# (or Production). The website no longer serves an APK.
#
# Usage:
#   ./build_play_bundle.sh [API_BASE_URL]
#
# Without an API base URL the app uses its built-in default,
# https://bestcpms.com/api.
#
# Signing: android/key.properties must point at the upload keystore:
#   storePassword=...
#   keyPassword=...
#   keyAlias=upload
#   storeFile=/absolute/path/to/bestcpms-upload.jks
# Keep the keystore and its password backed up outside the project.
#
# Before each upload, raise the build number in pubspec.yaml
# (version: 1.1.0+2 → 1.1.1+3). Play rejects a version code it has seen.

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

API_BASE_URL="${1:-${API_BASE_URL:-}}"

if ! command -v flutter >/dev/null 2>&1; then
  echo "ERROR: flutter is not installed or not on PATH." >&2
  exit 1
fi

if [ ! -f "android/key.properties" ]; then
  echo "ERROR: android/key.properties is missing, so the bundle can't be signed for Google Play." >&2
  echo "       Create the upload keystore first (see the comment at the top of this script)." >&2
  exit 1
fi

echo "==> Flutter: $(flutter --version | head -n 1)"
echo "==> Fetching packages"
flutter pub get

echo "==> Building signed release bundle"
if [ -n "$API_BASE_URL" ]; then
  echo "    API base URL: $API_BASE_URL"
  flutter build appbundle --release --dart-define=API_BASE_URL="$API_BASE_URL"
else
  echo "    API base URL: https://bestcpms.com/api (built-in default)"
  flutter build appbundle --release
fi

BUILT="build/app/outputs/bundle/release/app-release.aab"
if [ ! -f "$BUILT" ]; then
  echo "ERROR: expected bundle not found at $BUILT" >&2
  exit 1
fi

echo
echo "✓ Done: mobile/$BUILT"
echo "  Upload it in Play Console → Testing → Internal testing → Create new release."
