<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('telefones', function (Blueprint $table) {
            $table->string('unidade')->nullable()->after('nome');
            $table->string('telefone_externo')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('telefones', function (Blueprint $table) {
            $table->dropColumn(['unidade', 'telefone_externo']);
        });
    }
};
