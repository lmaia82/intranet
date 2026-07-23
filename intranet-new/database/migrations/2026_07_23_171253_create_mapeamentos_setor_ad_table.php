<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabela de/para: liga o setor bruto trazido do AD (ad_setor, que não
     * pode ser renomeado no AD por limitação) ao setor padronizado da
     * intranet, para permitir corrigir/atualizar usuarios em lote.
     */
    public function up(): void
    {
        Schema::create('mapeamentos_setor_ad', function (Blueprint $table) {
            $table->id();
            $table->string('ad_setor')->unique();
            $table->foreignId('sector_id')->constrained('sectors')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mapeamentos_setor_ad');
    }
};
