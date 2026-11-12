<?php

use App\Support\AppPages;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->json('pages')->nullable()->after('description');
        });

        foreach (DB::table('roles')->get(['id', 'slug']) as $role) {
            DB::table('roles')->where('id', $role->id)->update([
                'pages' => json_encode(AppPages::forSlug($role->slug)),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
            $table->dropColumn('pages');
        });
    }
};
