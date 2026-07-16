<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('arquivos', function (Blueprint $table) {
            $table->string('ocr_status')->nullable()->after('conteudo_ocr');
        });
    }

    public function down(): void
    {
        Schema::table('arquivos', function (Blueprint $table) {
            $table->dropColumn('ocr_status');
        });
    }
};
