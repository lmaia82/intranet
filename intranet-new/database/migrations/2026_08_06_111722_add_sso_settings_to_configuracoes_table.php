<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('configuracoes', function (Blueprint $table) {
            $table->unsignedInteger('sso_inatividade_minutos')->default(30);
            $table->unsignedInteger('sso_duracao_maxima_horas')->default(8);
            $table->boolean('sso_exigir_login_ao_fechar_navegador')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configuracoes', function (Blueprint $table) {
            $table->dropColumn(['sso_inatividade_minutos', 'sso_duracao_maxima_horas', 'sso_exigir_login_ao_fechar_navegador']);
        });
    }
};
