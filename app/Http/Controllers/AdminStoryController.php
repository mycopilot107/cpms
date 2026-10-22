<?php

namespace App\Http\Controllers;

use App\Models\SuccessStory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Site-administrator moderation for customer success stories. Every route
 * here sits behind the `story-admin` middleware, which restricts access to the
 * single account named by config('stories.admin_email').
 *
 * Nothing is public until an admin approves it here.
 */
class AdminStoryController extends Controller
{
    /** Queue view — filter by status (default: anything not yet approved). */
    public function index(Request $request): View
    {
        $status = $request->query('status', 'pending');
        if (! in_array($status, [...SuccessStory::STATUSES, 'all'], true)) {
            $status = 'pending';
        }

        $stories = SuccessStory::query()
            ->with(['company', 'submitter'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'pending'  => SuccessStory::query()->pending()->count(),
            'approved' => SuccessStory::query()->approved()->count(),
            'rejected' => SuccessStory::query()->where('status', SuccessStory::STATUS_REJECTED)->count(),
            'all'      => SuccessStory::query()->count(),
        ];

        return view('admin.stories.index', compact('stories', 'status', 'counts'));
    }

    /** Approve — publish to the public site. */
    public function approve(SuccessStory $story): RedirectResponse
    {
        $story->update([
            'status'       => SuccessStory::STATUS_APPROVED,
            'published_at' => $story->published_at ?? now(),
        ]);

        return back()->with('status', "Approved — \"{$story->name}\"'s story is now live.");
    }

    /** Reject — keep it out of the public site, but don't delete it. */
    public function reject(SuccessStory $story): RedirectResponse
    {
        $story->update([
            'status'       => SuccessStory::STATUS_REJECTED,
            'is_featured'  => false,
        ]);

        return back()->with('status', "Rejected \"{$story->name}\"'s story.");
    }

    /** Feature / un-feature — pins an approved story to the homepage spotlight. */
    public function feature(SuccessStory $story): RedirectResponse
    {
        if (! $story->isApproved()) {
            return back()->with('status', 'Only approved stories can be featured.');
        }

        // Only one featured story at a time — the homepage spotlight is single.
        if (! $story->is_featured) {
            SuccessStory::query()->where('is_featured', true)->update(['is_featured' => false]);
        }

        $story->update(['is_featured' => ! $story->is_featured]);

        return back()->with('status', $story->is_featured
            ? 'Featured on the homepage.'
            : 'Removed from the homepage spotlight.');
    }

    /** Permanently remove a story. */
    public function destroy(SuccessStory $story): RedirectResponse
    {
        $story->delete();

        return back()->with('status', 'Story deleted.');
    }
}
