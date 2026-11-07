<?php

namespace App\Policies;

use App\Models\DailyReport;
use App\Models\User;

/**
 * Anyone active in the company files their own daily reports. Planners and
 * finance/report roles read everyone's; others read only their own.
 */
class DailyReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    /** Whether the user sees every reporter's reports, not just their own. */
    public function viewAll(User $user): bool
    {
        return $user->canManagePlanning() || $user->canViewReports();
    }

    public function view(User $user, DailyReport $report): bool
    {
        return $this->sameCompany($user, $report)
            && ($report->user_id === $user->id || $this->viewAll($user));
    }

    public function create(User $user): bool
    {
        return $user->isActive();
    }

    public function update(User $user, DailyReport $report): bool
    {
        return $this->sameCompany($user, $report)
            && $user->isActive()
            && $report->user_id === $user->id
            && $report->isEditable();
    }

    private function sameCompany(User $user, DailyReport $report): bool
    {
        return $user->company_id !== null && $user->company_id === $report->company_id;
    }
}
