<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!DB::table('sectors')->where('name', 'CETEM')->exists()) {
            DB::table('sectors')->insert([
                'name' => 'CETEM',
                'quota_bytes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('sectors')->where('name', 'CETEM')->delete();
    }
};
