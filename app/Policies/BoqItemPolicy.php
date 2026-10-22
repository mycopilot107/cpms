<?php

namespace App\Policies;

use App\Models\BoqItem;
use App\Models\User;

class BoqItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canViewFinance();
    }

    public function view(User $user, BoqItem $item): bool
    {
        return $user->canViewFinance() && $this->sameCompany($user, $item);
    }

    public function create(User $user): bool
    {
        return $user->canManageFinance();
    }

    public function update(User $user, BoqItem $item): bool
    {
        return $user->canManageFinance() && $this->sameCompany($user, $item);
    }

    public function delete(User $user, BoqItem $item): bool
    {
        return $user->canManageFinance() && $this->sameCompany($user, $item);
    }

    private function sameCompany(User $user, BoqItem $item): bool
    {
        return $user->company_id !== null && $user->company_id === $item->company_id;
    }
}
