<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * The Best CPMS field app can be installed two ways: from Google Play (the
 * public listing, or the internal-testing link while it is being tested), or
 * as a direct APK download for phones without Play access.
 */
class DownloadController extends Controller
{
    /** Public landing page describing the app, with the install buttons. */
    public function app(): View
    {
        [$url, $mode] = $this->playLink();
        $apkPath = $this->apkPath();

        return view('public.download-app', [
            'playUrl' => $url,
            'playMode' => $mode,
            'apkAvailable' => $apkPath !== null,
            'apkSizeMb' => $apkPath !== null ? round(filesize($apkPath) / 1048576, 1) : null,
        ]);
    }

    /** Streams the APK, or falls back to Google Play when no APK is deployed. */
    public function android(): BinaryFileResponse|RedirectResponse
    {
        $apkPath = $this->apkPath();

        if ($apkPath !== null) {
            return response()->download($apkPath, config('mobile.apk_download_name'), [
                'Content-Type' => 'application/vnd.android.package-archive',
                'Cache-Control' => 'no-cache',
                'X-Robots-Tag' => 'noindex',
            ]);
        }

        [$url] = $this->playLink();

        if ($url === null) {
            return redirect()
                ->route('app.download-page')
                ->with('status', 'The Android app is not available for download yet. Please check back shortly.');
        }

        return redirect()->away($url);
    }

    private function apkPath(): ?string
    {
        $path = config('mobile.apk_path');

        return is_string($path) && is_file($path) ? $path : null;
    }

    /**
     * @return array{0: ?string, 1: ?string} URL and mode: 'store', 'testing', or null.
     */
    private function playLink(): array
    {
        $store = config('mobile.play_store_url');
        if (is_string($store) && $store !== '') {
            return [$store, 'store'];
        }

        $testing = config('mobile.play_testing_url');
        if (is_string($testing) && $testing !== '') {
            return [$testing, 'testing'];
        }

        return [null, null];
    }
}
