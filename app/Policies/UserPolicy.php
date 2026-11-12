<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /** Owners, admins, and any role whose pages include Team. */
    public function viewAny(User $user): bool
    {
        return $this->managesTeam($user);
    }

    public function view(User $user, User $model): bool
    {
        return $this->sameCompany($user, $model)
            && ($this->managesTeam($user) || $user->id === $model->id);
    }

    public function create(User $user): bool
    {
        return $this->managesTeam($user);
    }

    public function update(User $user, User $model): bool
    {
        if (! $this->sameCompany($user, $model)) {
            return false;
        }

        // The team form also sets role and status. Everyone edits their own
        // name, email, and phone under My account.
        if (! $this->managesTeam($user)) {
            return false;
        }

        // Only an owner may edit another owner or an admin.
        if (($model->isOwner() || $model->hasRole(User::ROLE_ADMIN)) && ! $user->isOwner()) {
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
        return $this->managesTeam($user)
            && ($user->isOwner() || ! $model->hasRole(User::ROLE_ADMIN));
    }

    /** Owners, admins, or a role that was given the Team page. */
    private function managesTeam(User $user): bool
    {
        return $user->isAdmin() || $user->hasPage('team');
    }

    /** Tenant guard: both users must belong to the same company. */
    private function sameCompany(User $a, User $b): bool
    {
        return $a->company_id !== null && $a->company_id === $b->company_id;
    }
}
