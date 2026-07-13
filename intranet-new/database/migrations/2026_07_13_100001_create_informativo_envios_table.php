<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('informativo_envios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('informativo_id')->constrained('informativos')->cascadeOnDelete();
            $table->string('email');
            $table->timestamp('enviado_em');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('informativo_envios');
    }
};
