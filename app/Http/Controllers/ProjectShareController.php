<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectShareLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * A project manager creates the weekly link for the client, copies it to
 * WhatsApp or email, and can switch old links off.
 */
class ProjectShareController extends Controller
{
    private const WEEKS_BACK = 8;

    /** Link lifetime choices, in days (null = until turned off). */
    private const EXPIRY_OPTIONS = ['30' => 30, '90' => 90, 'never' => null];

    public function index(Request $request, Project $project): View
    {
        $this->authorizeSharing($request, $project);

        return view('projects.share', [
            'project' => $project,
            'weeks' => $this->weeks(),
            'links' => ProjectShareLink::query()
                ->with('creator')
                ->where('project_id', $project->id)
                ->latest()
                ->get(),
            'newLinkId' => session('new_link_id'),
        ]);
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeSharing($request, $project);

        $validated = $request->validate([
            'week_start' => ['required', Rule::in(array_keys($this->weeks()))],
            'message' => ['nullable', 'string', 'max:1000'],
            'include_photos' => ['boolean'],
            'include_labour' => ['boolean'],
            'include_notes' => ['boolean'],
            'expires' => ['required', Rule::in(array_keys(self::EXPIRY_OPTIONS))],
        ]);

        $weekStart = Carbon::parse($validated['week_start'])->startOfDay();
        $days = self::EXPIRY_OPTIONS[$validated['expires']];

        $link = ProjectShareLink::generate($project, $request->user());
        $link->fill([
            'week_start' => $weekStart,
            'week_end' => $weekStart->copy()->addDays(6),
            'message' => isset($validated['message']) && trim($validated['message']) !== '' ? trim($validated['message']) : null,
            'include_photos' => $request->boolean('include_photos'),
            'include_labour' => $request->boolean('include_labour'),
            'include_notes' => $request->boolean('include_notes'),
            'expires_at' => $days === null ? null : now()->addDays($days),
        ])->save();

        return redirect()
            ->route('projects.share', $project)
            ->with('status', 'Link ready. Send it on WhatsApp or by email.')
            ->with('new_link_id', $link->id);
    }

    public function revoke(Request $request, ProjectShareLink $link): RedirectResponse
    {
        $project = $link->project;
        $this->authorizeSharing($request, $project);

        $link->forceFill(['revoked_at' => now()])->save();

        return redirect()->route('projects.share', $project)->with('status', 'Link turned off. The client can no longer open it.');
    }

    private function authorizeSharing(Request $request, Project $project): void
    {
        $this->authorize('view', $project);
        abort_unless($request->user()->canManagePlanning(), 403, 'Only owners, admins, managers and supervisors can share project updates.');
    }

    /**
     * Monday date => "This week (3–9 Nov)".
     *
     * @return array<string, string>
     */
    private function weeks(): array
    {
        $weeks = [];
        $monday = today()->startOfWeek(Carbon::MONDAY);

        for ($i = 0; $i < self::WEEKS_BACK; $i++) {
            $start = $monday->copy()->subWeeks($i);
            $end = $start->copy()->addDays(6);
            $range = $start->month === $end->month
                ? $start->format('j').'–'.$end->format('j M')
                : $start->format('j M').' – '.$end->format('j M');

            $weeks[$start->toDateString()] = match ($i) {
                0 => "This week ({$range})",
                1 => "Last week ({$range})",
                default => $range.' '.$start->format('Y'),
            };
        }

        return $weeks;
    }
}
