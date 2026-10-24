<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Replace the users.role enum with a roles table and users.role_id.
     *
     * Existing rows are copied by slug before the old column is removed.
     * Ids are fixed so a user's role_id means the same role on every install.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description');
            $table->unsignedSmallInteger('sort_order');
            $table->timestamps();
        });

        $now = now();

        DB::table('roles')->insert(array_map(
            fn (array $role): array => $role + ['created_at' => $now, 'updated_at' => $now],
            $this->roles(),
        ));

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'role']);
            $table->foreignId('role_id')->nullable()->after('password')->constrained('roles')->restrictOnDelete();
        });

        foreach (DB::table('roles')->pluck('id', 'slug') as $slug => $id) {
            DB::table('users')->where('role', $slug)->update(['role_id' => $id]);
        }

        $workerId = DB::table('roles')->where('slug', 'worker')->value('id');
        DB::table('users')->whereNull('role_id')->update(['role_id' => $workerId]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->nullable(false)->change();
            $table->index(['company_id', 'role_id']);
        });
    }

    /**
     * Restore the enum column from roles.slug. Role descriptions are dropped
     * with the roles table.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'role_id']);
            $table->enum('role', [
                'owner',
                'admin',
                'manager',
                'engineer',
                'supervisor',
                'worker',
                'accountant',
            ])->default('worker')->after('password');
        });

        foreach (DB::table('roles')->pluck('slug', 'id') as $id => $slug) {
            DB::table('users')->where('role_id', $id)->update(['role' => $slug]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
            $table->index(['company_id', 'role']);
        });

        Schema::dropIfExists('roles');
    }

    /**
     * @return list<array{id: int, slug: string, name: string, description: string, sort_order: int}>
     */
    private function roles(): array
    {
        return [
            [
                'id' => 1,
                'slug' => 'owner',
                'name' => 'Owner',
                'sort_order' => 1,
                'description' => 'Registers the company and holds the account. Manages billing, the team, projects, the programme, finance, and reports. Only the owner can change an admin or another owner. This account cannot be deleted or given a different role.',
            ],
            [
                'id' => 2,
                'slug' => 'admin',
                'name' => 'Admin',
                'sort_order' => 2,
                'description' => 'Runs the company day to day: billing, team members, projects, milestones, tasks, finance, and reports. Cannot change the owner, and cannot edit or remove another admin.',
            ],
            [
                'id' => 3,
                'slug' => 'manager',
                'name' => 'Project manager',
                'sort_order' => 3,
                'description' => 'Creates and edits projects, milestones, and tasks, including tasks assigned to someone else. Opens the bill of quantities, expenses, payments, and reports. Does not manage the team, billing, or deleting a project.',
            ],
            [
                'id' => 4,
                'slug' => 'engineer',
                'name' => 'Engineer',
                'sort_order' => 4,
                'description' => 'Sees the company\'s projects and updates tasks assigned to them: status, percent complete, and hours. This is the same access as a site worker, with a separate label for site engineers. Does not plan other people\'s work, and does not open finance or reports.',
            ],
            [
                'id' => 5,
                'slug' => 'supervisor',
                'name' => 'Supervisor',
                'sort_order' => 5,
                'description' => 'Plans the programme: creates and edits milestones and any task. Sees every project. Does not create projects, and does not open finance, reports, billing, or the team roster.',
            ],
            [
                'id' => 6,
                'slug' => 'accountant',
                'name' => 'Accountant',
                'sort_order' => 6,
                'description' => 'Keeps the commercial record: the bill of quantities, project budget, expenses, payments, and the cost and progress reports. Sees projects and tasks. Does not plan the programme, manage the team, or change billing.',
            ],
            [
                'id' => 7,
                'slug' => 'worker',
                'name' => 'Site worker',
                'sort_order' => 7,
                'description' => 'Sees the company\'s projects and the tasks assigned to them. Updates status, percent complete, and hours on those tasks from the office or the phone. Does not open finance, reports, planning for other people, or the team roster.',
            ],
        ];
    }
};
