<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * The Best CPMS field app is installed from Google Play — the public listing
 * once it is live, or the internal-testing link while it is being tested.
 * The site never serves an APK, so no one is asked to allow "unknown sources".
 */
class DownloadController extends Controller
{
    /** Public landing page describing the app, with the Google Play button. */
    public function app(): View
    {
        [$url, $mode] = $this->playLink();

        return view('public.download-app', [
            'playUrl' => $url,
            'playMode' => $mode,
        ]);
    }

    /** Old /download/android links (shared before) now go to Google Play. */
    public function android(): RedirectResponse
    {
        [$url] = $this->playLink();

        if ($url === null) {
            return redirect()
                ->route('app.download-page')
                ->with('status', 'The Android app is not on Google Play yet. Please check back shortly.');
        }

        return redirect()->away($url);
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
