<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('arquivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pasta_id')->nullable()->constrained('pastas')->cascadeOnDelete();
            $table->string('nome_original');
            $table->string('caminho');
            $table->string('extensao', 20);
            $table->unsignedBigInteger('tamanho')->default(0);
            $table->text('descricao')->nullable();
            $table->foreignId('sector_id')->nullable()->constrained('sectors')->nullOnDelete();
            $table->boolean('is_private')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('arquivos');
    }
};
