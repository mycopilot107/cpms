<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /** Only owners/admins manage the team roster. */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, User $model): bool
    {
        return $this->sameCompany($user, $model)
            && ($user->isAdmin() || $user->id === $model->id);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $model): bool
    {
        if (! $this->sameCompany($user, $model)) {
            return false;
        }

        // Anyone may edit their own profile.
        if ($user->id === $model->id) {
            return true;
        }

        if (! $user->isAdmin()) {
            return false;
        }

        // Only an owner may edit another owner or an admin.
        if (($model->isOwner() || $model->role === User::ROLE_ADMIN) && ! $user->isOwner()) {
            return false;
        }

        return true;
    }

    public function delete(User $user, User $model): bool
    {
        if (! $this->sameCompany($user, $model)) {
            return false;
        }

        // Never delete yourself or the company owner.
        if ($user->id === $model->id || $model->isOwner()) {
            return false;
        }

        // Admins can delete non-admins; only the owner can delete an admin.
        return $user->isAdmin()
            && ($user->isOwner() || $model->role !== User::ROLE_ADMIN);
    }

    /** Tenant guard: both users must belong to the same company. */
    private function sameCompany(User $a, User $b): bool
    {
        return $a->company_id !== null && $a->company_id === $b->company_id;
    }
}
