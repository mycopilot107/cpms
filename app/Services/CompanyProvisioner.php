<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Creates a tenant the same way everywhere: the company, its owner account,
 * and the trial subscription row, in one transaction so a half-made company
 * can never exist.
 */
class CompanyProvisioner
{
    /**
     * @param  array<string, mixed>  $company  Fillable Company attributes.
     * @param  array{name: string, email: string, password: string, phone?: ?string}  $owner
     * @param  ?int  $trialDays  Exact trial length; null uses the standard rule (Company::trialEndsAt).
     */
    public function provision(array $company, array $owner, ?int $trialDays = null): User
    {
        return DB::transaction(function () use ($company, $owner, $trialDays): User {
            $now = now();
            $trialEndsAt = $trialDays === null ? Company::trialEndsAt($now) : $now->copy()->addDays($trialDays);

            $tenant = Company::create($company + [
                'status' => Company::STATUS_ACTIVE,
                'trial_started_at' => $now,
                'trial_ends_at' => $trialEndsAt,
            ]);

            // company_id / role_id / status are guarded, so set them explicitly.
            $user = new User;
            $user->company_id = $tenant->id;
            $user->name = $owner['name'];
            $user->email = $owner['email'];
            $user->phone = $owner['phone'] ?? null;
            $user->password = $owner['password'];
            $user->assignRole(User::ROLE_OWNER);
            $user->status = 'active';
            $user->email_verified_at = $now;
            $user->save();

            Subscription::create([
                'company_id' => $tenant->id,
                'plan' => Subscription::PLAN_TRIAL,
                'status' => Subscription::STATUS_TRIAL,
                'trial_starts_at' => $now,
                'trial_ends_at' => $trialEndsAt,
            ]);

            return $user;
        });
    }
}
