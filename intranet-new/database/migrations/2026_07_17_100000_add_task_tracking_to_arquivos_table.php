<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('arquivos', function (Blueprint $table) {
            $table->string('paperless_task_id')->nullable()->after('ocr_status');
            $table->text('ocr_erro')->nullable()->after('paperless_task_id');
        });
    }

    public function down(): void
    {
        Schema::table('arquivos', function (Blueprint $table) {
            $table->dropColumn(['paperless_task_id', 'ocr_erro']);
        });
    }
};
