<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('telefones', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('telefone');
            $table->foreignId('sector_id')->constrained('sectors');
            $table->string('email')->nullable();
            $table->string('cargo')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('telefones');
    }
};
