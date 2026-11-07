<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Every user across all companies, for support lookups. Read-only. */
class PlatformUserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $roles = Role::query()->orderBy('sort_order')->get();

        $role = (string) $request->query('role', 'all');
        if ($role !== 'all' && ! $roles->contains('slug', $role)) {
            $role = 'all';
        }

        $status = (string) $request->query('status', 'all');
        if (! in_array($status, ['all', 'active', 'inactive'], true)) {
            $status = 'all';
        }

        $users = User::query()
            ->with(['company', 'role'])
            ->when($search !== '', function ($query) use ($search): void {
                $term = '%'.$search.'%';
                $query->where(fn ($inner) => $inner
                    ->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhereHas('company', fn ($c) => $c->where('name', 'like', $term)));
            })
            ->when($role !== 'all', fn ($query) => $query->whereHas('role', fn ($r) => $r->where('slug', $role)))
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('platform.users.index', [
            'users' => $users,
            'roles' => $roles,
            'filters' => ['q' => $search, 'role' => $role, 'status' => $status],
        ]);
    }
}
