<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Serves the Best CPMS field app download — a public landing page plus the APK file
 * itself. The app is the Flutter companion for field workers (view assigned
 * tasks; report status, progress and actual hours from site).
 *
 * The APK is deliberately NOT committed to the repo. It is produced locally
 * with `mobile/build_apk.sh` (or the Windows `.bat`) and dropped into
 * `public/downloads/`. Every method here handles the "not built yet" case
 * gracefully, so the link on the login page never 404s.
 */
class DownloadController extends Controller
{
    /**
     * The one filename we ever serve. The path is built from this constant and
     * `public_path()` only — there is no filename request parameter, so there is
     * no path-traversal surface to worry about.
     */
    private const APK_FILENAME = 'cpms-field-app.apk';

    /** Absolute, server-controlled path to the Android build. */
    protected function apkPath(): string
    {
        return public_path('downloads/'.self::APK_FILENAME);
    }

    /** Public landing page describing the app, with a download button. */
    public function app(): View
    {
        $path      = $this->apkPath();
        $available = is_file($path);

        return view('public.download-app', [
            'available'  => $available,
            'apkSize'    => $available ? $this->humanSize((int) filesize($path)) : null,
            'apkUpdated' => $available ? Carbon::createFromTimestamp(filemtime($path)) : null,
        ]);
    }

    /** Stream the APK as a download, or bounce back with a notice if absent. */
    public function android(): BinaryFileResponse|RedirectResponse
    {
        $path = $this->apkPath();

        if (! is_file($path)) {
            return redirect()
                ->route('app.download-page')
                ->with('status', 'The Android app isn’t available to download yet. Please check back shortly.');
        }

        return response()->download($path, 'BestCPMS-Field.apk', [
            'Content-Type' => 'application/vnd.android.package-archive',
        ]);
    }

    /** Human-readable file size (e.g. "18.4 MB"). */
    protected function humanSize(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $i     = min((int) floor(log($bytes, 1024)), count($units) - 1);

        return round($bytes / (1024 ** $i), 1).' '.$units[$i];
    }
}
