<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveRoleRequest;
use App\Models\Role;
use App\Support\AppPages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Company roles. Built-in roles are listed but only a role created for this
 * company can be edited or removed. Page checkboxes decide which menus open.
 */
class RoleController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->isAdmin(), 403);

        $roles = Role::query()
            ->forCompany($request->user()->company_id)
            ->withCount(['users' => fn ($query) => $query->where('company_id', $request->user()->company_id)])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('roles.index', [
            'roles' => $roles,
            'pages' => AppPages::LABELS,
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->isAdmin(), 403);

        return view('roles.create', ['pages' => AppPages::LABELS]);
    }

    public function store(SaveRoleRequest $request): RedirectResponse
    {
        $role = new Role([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'pages' => $request->pages(),
            'slug' => $this->slug($request->validated('name'), $request->user()->company_id),
            'sort_order' => (int) Role::query()->max('sort_order') + 1,
        ]);
        $role->company_id = $request->user()->company_id;
        $role->save();

        return redirect()
            ->route('roles.index')
            ->with('status', 'Role created.');
    }

    public function edit(Request $request, Role $role): View
    {
        $this->ownRole($request, $role);

        return view('roles.edit', [
            'role' => $role,
            'pages' => AppPages::LABELS,
        ]);
    }

    public function update(SaveRoleRequest $request, Role $role): RedirectResponse
    {
        $this->ownRole($request, $role);

        $role->fill([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'pages' => $request->pages(),
        ])->save();

        return redirect()
            ->route('roles.index')
            ->with('status', 'Role updated.');
    }

    public function destroy(Request $request, Role $role): RedirectResponse
    {
        $this->ownRole($request, $role);

        if ($role->users()->exists()) {
            return back()->withErrors(['role' => 'This role is still assigned to someone. Move those people to another role first.']);
        }

        $role->delete();

        return redirect()
            ->route('roles.index')
            ->with('status', 'Role deleted.');
    }

    private function ownRole(Request $request, Role $role): void
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_unless(! $role->isSystem() && $role->company_id === $request->user()->company_id, 404);
    }

    private function slug(string $name, int $companyId): string
    {
        $base = Str::slug($name);
        $base = $base !== '' ? $base : 'role';
        $slug = $base.'-c'.$companyId;
        $suffix = 2;

        while (Role::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-c'.$companyId.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
