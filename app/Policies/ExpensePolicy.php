<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;

class ExpensePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canViewFinance();
    }

    public function view(User $user, Expense $expense): bool
    {
        return $user->canViewFinance() && $this->sameCompany($user, $expense);
    }

    public function create(User $user): bool
    {
        return $user->canManageFinance();
    }

    public function update(User $user, Expense $expense): bool
    {
        return $user->canManageFinance() && $this->sameCompany($user, $expense);
    }

    public function delete(User $user, Expense $expense): bool
    {
        return $user->canManageFinance() && $this->sameCompany($user, $expense);
    }

    private function sameCompany(User $user, Expense $expense): bool
    {
        return $user->company_id !== null && $user->company_id === $expense->company_id;
    }
}
