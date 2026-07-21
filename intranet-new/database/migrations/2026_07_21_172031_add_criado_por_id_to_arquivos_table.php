<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('arquivos', function (Blueprint $table) {
            $table->foreignId('criado_por_id')->nullable()->after('pasta_id')->constrained('users')->nullOnDelete();
        });
    }
    public function down(): void {
        Schema::table('arquivos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('criado_por_id');
        });
    }
};
