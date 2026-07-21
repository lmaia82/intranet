<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('informativos', function (Blueprint $table) {
            $table->foreignId('arquivo_id')->nullable()->after('image')->constrained('arquivos')->nullOnDelete();
        });
    }
    public function down(): void {
        Schema::table('informativos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('arquivo_id');
        });
    }
};
