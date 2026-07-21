<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('destaques', function (Blueprint $table) {
            $table->string('imagem')->nullable()->change();
        });
    }
    public function down(): void {
        Schema::table('destaques', function (Blueprint $table) {
            $table->string('imagem')->nullable(false)->change();
        });
    }
};
