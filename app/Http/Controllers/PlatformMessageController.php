<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Contact-form enquiries and customer feedback from the public site. */
class PlatformMessageController extends Controller
{
    public function index(Request $request): View
    {
        $type = (string) $request->query('type', 'all');
        if (! in_array($type, ['all', ContactMessage::TYPE_CONTACT, ContactMessage::TYPE_FEEDBACK, 'unread'], true)) {
            $type = 'all';
        }

        $search = trim((string) $request->query('q', ''));

        $messages = ContactMessage::query()
            ->when($type === 'unread', fn ($q) => $q->whereNull('read_at'))
            ->when(in_array($type, [ContactMessage::TYPE_CONTACT, ContactMessage::TYPE_FEEDBACK], true), fn ($q) => $q->where('type', $type))
            ->when($search !== '', function ($query) use ($search): void {
                $term = '%'.$search.'%';
                $query->where(fn ($inner) => $inner
                    ->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('company', 'like', $term)
                    ->orWhere('subject', 'like', $term));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('platform.messages.index', [
            'messages' => $messages,
            'type' => $type,
            'search' => $search,
            'counts' => [
                'all' => ContactMessage::query()->count(),
                'unread' => ContactMessage::query()->whereNull('read_at')->count(),
                ContactMessage::TYPE_CONTACT => ContactMessage::query()->where('type', ContactMessage::TYPE_CONTACT)->count(),
                ContactMessage::TYPE_FEEDBACK => ContactMessage::query()->where('type', ContactMessage::TYPE_FEEDBACK)->count(),
            ],
        ]);
    }

    /** Opening a message marks it read. */
    public function show(ContactMessage $message): View
    {
        if ($message->read_at === null) {
            $message->forceFill(['read_at' => now()])->save();
        }

        return view('platform.messages.show', ['message' => $message]);
    }
}
