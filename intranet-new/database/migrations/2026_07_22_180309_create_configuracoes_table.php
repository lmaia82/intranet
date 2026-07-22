<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('configuracoes', function (Blueprint $table) {
            $table->id();
            $table->boolean('previa_login_ativa')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('configuracoes');
    }
};
