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
            // Ativado por padrão — a tela de Tutoriais já existe e funciona;
            // o botão é só pra permitir desativá-la enquanto o CETEM avalia
            // se vai manter essa página.
            $table->boolean('tutoriais_ativo')->default(true)->after('tempo_inatividade_minutos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configuracoes', function (Blueprint $table) {
            $table->dropColumn('tutoriais_ativo');
        });
    }
};
