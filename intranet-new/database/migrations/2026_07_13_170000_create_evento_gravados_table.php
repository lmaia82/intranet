<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('evento_gravados', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->date('data');
            $table->string('youtube_url');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('evento_gravados');
    }
};
