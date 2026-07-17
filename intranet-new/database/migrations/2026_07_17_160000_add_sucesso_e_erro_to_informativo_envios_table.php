<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('informativo_envios', function (Blueprint $table) {
            $table->boolean('sucesso')->default(true)->after('email');
            $table->text('erro')->nullable()->after('sucesso');
        });
    }

    public function down(): void
    {
        Schema::table('informativo_envios', function (Blueprint $table) {
            $table->dropColumn(['sucesso', 'erro']);
        });
    }
};
