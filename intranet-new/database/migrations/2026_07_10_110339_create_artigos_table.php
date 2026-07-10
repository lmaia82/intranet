<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('artigos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->unsignedSmallInteger('ano');
            $table->string('autores');
            $table->string('palavras_chave')->nullable();
            $table->string('arquivo');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('artigos');
    }
};
