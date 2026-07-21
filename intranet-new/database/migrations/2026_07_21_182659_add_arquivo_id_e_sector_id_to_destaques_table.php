<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('destaques', function (Blueprint $table) {
            $table->foreignId('arquivo_id')->nullable()->after('imagem')->constrained('arquivos')->nullOnDelete();
            $table->foreignId('sector_id')->nullable()->after('arquivo_id')->constrained('sectors')->nullOnDelete();
        });
    }
    public function down(): void {
        Schema::table('destaques', function (Blueprint $table) {
            $table->dropConstrainedForeignId('arquivo_id');
            $table->dropConstrainedForeignId('sector_id');
        });
    }
};
