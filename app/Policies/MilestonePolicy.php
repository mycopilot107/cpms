<?php

namespace App\Policies;

use App\Models\Milestone;
use App\Models\User;

class MilestonePolicy
{
    /** Any active member of the company can view planning milestones. */
    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    public function view(User $user, Milestone $milestone): bool
    {
        return $user->isActive() && $this->sameCompany($user, $milestone);
    }

    public function create(User $user): bool
    {
        return $user->canManagePlanning();
    }

    public function update(User $user, Milestone $milestone): bool
    {
        return $user->canManagePlanning() && $this->sameCompany($user, $milestone);
    }

    public function delete(User $user, Milestone $milestone): bool
    {
        return $user->canManagePlanning() && $this->sameCompany($user, $milestone);
    }

    private function sameCompany(User $user, Milestone $milestone): bool
    {
        return $user->company_id !== null && $user->company_id === $milestone->company_id;
    }
}
