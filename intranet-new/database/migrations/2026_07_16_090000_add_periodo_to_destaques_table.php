<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('destaques', function (Blueprint $table) {
            $table->dateTime('inicio_em')->nullable()->after('link');
            $table->dateTime('fim_em')->nullable()->after('inicio_em');
        });

        DB::table('destaques')->whereNull('inicio_em')->update([
            'inicio_em' => DB::raw('created_at'),
        ]);
    }

    public function down(): void
    {
        Schema::table('destaques', function (Blueprint $table) {
            $table->dropColumn(['inicio_em', 'fim_em']);
        });
    }
};
