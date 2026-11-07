<?php

use App\Models\Company;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Give companies that are still on the old 15-day trial the new trial
     * length (at least 30 days and past the first month-end). Only extends —
     * a trial is never shortened, and expired trials are left alone.
     */
    public function up(): void
    {
        $companies = DB::table('companies')
            ->whereNotNull('trial_started_at')
            ->where('trial_ends_at', '>', now())
            ->get(['id', 'trial_started_at', 'trial_ends_at']);

        foreach ($companies as $company) {
            $newEnd = Company::trialEndsAt(Carbon::parse($company->trial_started_at));

            if ($newEnd->greaterThan(Carbon::parse($company->trial_ends_at))) {
                DB::table('companies')->where('id', $company->id)->update(['trial_ends_at' => $newEnd]);
                DB::table('subscriptions')
                    ->where('company_id', $company->id)
                    ->where('status', 'trial')
                    ->update(['trial_ends_at' => $newEnd]);
            }
        }
    }

    /** Extended trials are kept; shortening a customer's trial on rollback would be unfair. */
    public function down(): void
    {
        //
    }
};
