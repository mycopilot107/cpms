<?php

namespace App\Policies;

use App\Models\ProjectPayment;
use App\Models\User;

class ProjectPaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canViewFinance();
    }

    public function view(User $user, ProjectPayment $payment): bool
    {
        return $user->canViewFinance() && $this->sameCompany($user, $payment);
    }

    public function create(User $user): bool
    {
        return $user->canManageFinance();
    }

    public function update(User $user, ProjectPayment $payment): bool
    {
        return $user->canManageFinance() && $this->sameCompany($user, $payment);
    }

    public function delete(User $user, ProjectPayment $payment): bool
    {
        return $user->canManageFinance() && $this->sameCompany($user, $payment);
    }

    private function sameCompany(User $user, ProjectPayment $payment): bool
    {
        return $user->company_id !== null && $user->company_id === $payment->company_id;
    }
}
