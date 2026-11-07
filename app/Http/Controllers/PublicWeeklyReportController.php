<?php

namespace App\Http\Controllers;

use App\Models\ProjectShareLink;
use App\Services\PhotoStore;
use App\Services\WeeklyProjectReport;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The client's view of a weekly update — no login, just the link. An unknown,
 * revoked, or expired token looks the same: "this link is not available".
 */
class PublicWeeklyReportController extends Controller
{
    public function __construct(private readonly WeeklyProjectReport $report) {}

    public function show(Request $request, string $token): View|Response
    {
        $link = ProjectShareLink::findUsable($token);
        if ($link === null) {
            return response()->view('public.weekly-update-unavailable', [], 404)->header('X-Robots-Tag', 'noindex, nofollow');
        }

        // Count client opens, not the team checking its own preview.
        if ($request->user()?->company_id !== $link->company_id) {
            ProjectShareLink::query()->withoutGlobalScopes()->whereKey($link->id)
                ->increment('view_count', 1, ['last_viewed_at' => now()]);
        }

        return response()
            ->view('public.weekly-update', $this->report->build($link))
            ->header('X-Robots-Tag', 'noindex, nofollow')
            ->header('Referrer-Policy', 'no-referrer');
    }

    public function photo(Request $request, string $token, string $kind, int $photo): StreamedResponse
    {
        $link = ProjectShareLink::findUsable($token);
        abort_if($link === null, 404);

        $record = $this->report->photoBelongsToLink($link, $kind, $photo);
        abort_if($record === null, 404);

        return Storage::disk(PhotoStore::DISK)->response(
            $request->query('size') === 'thumb' ? $record->thumb_path : $record->path,
            headers: ['Cache-Control' => 'private, max-age=3600', 'X-Robots-Tag' => 'noindex'],
        );
    }
}
