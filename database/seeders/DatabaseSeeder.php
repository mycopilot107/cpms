<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Project;
use App\Models\Subscription;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed one demo tenant: a company, its team, a 15-day trial subscription,
     * and a handful of projects with tasks — all created atomically inside a
     * transaction, mirroring the real company-registration flow.
     *
     * No real secrets here: every demo password is the well-known local dev
     * value "password". Change them before using this anywhere real.
     */
    public function run(): void
    {
        $ownerEmail = 'owner@demo.test';

        if (User::where('email', $ownerEmail)->exists()) {
            $this->command?->warn("Demo owner {$ownerEmail} already exists — skipping seed.");

            return;
        }

        DB::transaction(function () use ($ownerEmail) {
            $now = now();
            $trialEnds = $now->copy()->addDays(Company::TRIAL_DAYS);

            $company = Company::create([
                'name' => 'ABC Construction',
                'email' => 'hello@abc-construction.test',
                'phone' => '+91-9876543210',
                'address' => '123 Site Road',
                'city' => 'Pune',
                'state' => 'Maharashtra',
                'country' => 'India',
                'gst_number' => '27ABCDE1234F1Z5',
                'status' => Company::STATUS_ACTIVE,
                'trial_started_at' => $now,
                'trial_ends_at' => $trialEnds,
            ]);

            // company_id / role / status are guarded on the User model, so we
            // set them explicitly. Plaintext passwords are hashed by the cast.
            $owner = $this->member($company, 'Demo Owner', $ownerEmail, User::ROLE_OWNER);
            $manager = $this->member($company, 'Priya Manager', 'manager@demo.test', User::ROLE_MANAGER);
            $engineer = $this->member($company, 'Ravi Engineer', 'engineer@demo.test', User::ROLE_ENGINEER);
            $supervisor = $this->member($company, 'Sana Supervisor', 'supervisor@demo.test', User::ROLE_SUPERVISOR);
            $worker = $this->member($company, 'Ganesh Worker', 'worker@demo.test', User::ROLE_WORKER);

            Subscription::create([
                'company_id' => $company->id,
                'plan' => Subscription::PLAN_TRIAL,
                'status' => Subscription::STATUS_TRIAL,
                'trial_starts_at' => $now,
                'trial_ends_at' => $trialEnds,
            ]);

            // Demo projects + tasks. company_id is set by the factory states
            // (the trait's auto-stamp is inactive while seeding — no auth user).
            $teamIds = [$owner->id, $manager->id, $engineer->id, $supervisor->id, $worker->id];

            Project::factory()
                ->count(3)
                ->forCompany($company)
                ->create(['created_by' => $owner->id])
                ->each(function (Project $project) use ($teamIds, $owner) {
                    Task::factory()
                        ->count(random_int(4, 7))
                        ->forProject($project)
                        ->create([
                            'created_by' => $owner->id,
                            'assigned_to' => fn () => Arr::random($teamIds),
                        ]);
                });
        });

        $this->command?->info('Seeded demo tenant "ABC Construction".');
        $this->command?->info("  Owner login : {$ownerEmail}  /  password");
        $this->command?->info('  Also: manager@demo.test, engineer@demo.test, supervisor@demo.test, worker@demo.test (all "password")');
    }

    /**
     * Create a company member, assigning the guarded fields explicitly.
     */
    private function member(Company $company, string $name, string $email, string $role): User
    {
        $user = new User([
            'name' => $name,
            'email' => $email,
            'password' => 'password',
        ]);
        $user->company_id = $company->id;
        $user->assignRole($role);
        $user->status = 'active';
        $user->email_verified_at = now();
        $user->save();

        return $user;
    }
}
