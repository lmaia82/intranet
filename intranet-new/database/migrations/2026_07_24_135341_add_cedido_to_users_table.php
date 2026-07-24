<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Marca usuários cedidos para outro órgão — ao ser marcado, o usuário
     * é desativado automaticamente (ver AdminController::toggleCedido /
     * marcarCedidoUsuariosLote).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('cedido')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('cedido');
        });
    }
};
