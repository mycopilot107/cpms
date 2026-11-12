<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Built-in roles were inserted with fixed ids, so Postgres kept the id
     * sequence at 1 and the next created role collided with Owner.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement("SELECT setval(pg_get_serial_sequence('roles', 'id'), (SELECT MAX(id) FROM roles))");
    }

    public function down(): void
    {
        // The sequence can stay at the highest id.
    }
};
