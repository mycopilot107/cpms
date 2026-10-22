<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSuccessStoryRequest;
use App\Models\SuccessStory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Signed-in users submit a customer success story here. Submissions are
 * queued as `pending` — a site administrator approves them before anything
 * appears on the public homepage or reviews page (see AdminStoryController).
 */
class StoryController extends Controller
{
    /** The "share your story" form plus the user's own past submissions. */
    public function index(): View
    {
        $user = auth()->user();

        $myStories = SuccessStory::query()
            ->where('submitted_by', $user->id)
            ->latest()
            ->get();

        return view('stories.index', compact('myStories'));
    }

    /** Persist a new submission as pending. */
    public function store(StoreSuccessStoryRequest $request): RedirectResponse
    {
        $user = $request->user();

        $story = new SuccessStory($request->safe()->only([
            'name', 'role', 'organisation', 'email', 'rating', 'story',
        ]));

        // Moderation + ownership are set server-side only — never trusted from
        // the form. A story is always pending until an admin approves it.
        $story->status       = SuccessStory::STATUS_PENDING;
        $story->is_featured  = false;
        $story->company_id   = $user->company_id;
        $story->submitted_by = $user->id;
        $story->save();

        return redirect()
            ->route('stories.index')
            ->with('status', 'Thank you! Your story has been sent to the team for review — we\'ll publish it once it\'s approved.');
    }
}
