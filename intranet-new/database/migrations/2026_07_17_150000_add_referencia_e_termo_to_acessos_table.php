<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('acessos', function (Blueprint $table) {
            $table->string('referencia_tipo')->nullable()->after('modulo');
            $table->unsignedBigInteger('referencia_id')->nullable()->after('referencia_tipo');
            $table->string('termo')->nullable()->after('referencia_id');
            $table->unsignedInteger('resultados')->nullable()->after('termo');

            $table->index(['referencia_tipo', 'referencia_id']);
        });
    }

    public function down(): void
    {
        Schema::table('acessos', function (Blueprint $table) {
            $table->dropColumn(['referencia_tipo', 'referencia_id', 'termo', 'resultados']);
        });
    }
};
