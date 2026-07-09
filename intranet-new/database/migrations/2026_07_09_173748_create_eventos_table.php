<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('local');
            $table->text('informacoes')->nullable();
            $table->date('dt_start');
            $table->date('dt_end')->nullable();
            $table->time('tm_start')->nullable();
            $table->time('tm_end')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('eventos');
    }
};
