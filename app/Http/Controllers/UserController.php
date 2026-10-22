<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    /** Roles that can be assigned to team members (everyone but the owner). */
    private function assignableRoles(): array
    {
        return array_values(array_diff(User::ROLES, [User::ROLE_OWNER]));
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        // Users have no global scope — scope to the current company explicitly.
        $users = User::where('company_id', $request->user()->company_id)
            ->orderByRaw("FIELD(role, 'owner', 'admin', 'manager', 'engineer', 'supervisor', 'accountant', 'worker')")
            ->orderBy('name')
            ->paginate(15);

        return view('users.index', ['users' => $users]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('users.create', ['roles' => $this->assignableRoles()]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // company_id / role / status are guarded on the model, so assign them
        // explicitly from validated data + the current tenant.
        $user = new User();
        $user->company_id = $request->user()->company_id;
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->phone = $data['phone'] ?? null;
        $user->password = $data['password']; // hashed by the model cast
        $user->role = $data['role'];
        $user->status = $data['status'];
        $user->email_verified_at = now();
        $user->save();

        return redirect()
            ->route('users.index')
            ->with('status', 'Team member added.');
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('users.edit', [
            'user' => $user,
            'roles' => $this->assignableRoles(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->phone = $data['phone'] ?? null;

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        // The owner's role/status are immutable through this form.
        if (! $user->isOwner()) {
            $user->role = $data['role'];
            $user->status = $data['status'];
        }

        $user->save();

        return redirect()
            ->route('users.index')
            ->with('status', 'Team member updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('status', 'Team member removed.');
    }
}
