<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('arquivos', function (Blueprint $table) {
            $table->date('data')->nullable()->after('descricao');
        });

        DB::table('arquivos')->whereNull('data')->update([
            'data' => DB::raw('DATE(created_at)'),
        ]);
    }

    public function down(): void
    {
        Schema::table('arquivos', function (Blueprint $table) {
            $table->dropColumn('data');
        });
    }
};
