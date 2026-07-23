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
        Schema::table('sectors', function (Blueprint $table) {
            // Um setor "serviço" pertence a um setor "coordenação" — hierarquia
            // de 2 níveis do organograma do CETEM (Diretoria > Coordenações >
            // Serviços). Nulo = topo (coordenação, ou a diretoria).
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('sectors')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sectors', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
        });
    }
};
