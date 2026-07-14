<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        DB::table('permissions')->where('key', 'artigos.ver')->update(['label' => 'Ver Publicações']);
        DB::table('permissions')->where('key', 'artigos.criar')->update(['label' => 'Criar/editar em Publicações']);
    }
    public function down(): void {
        DB::table('permissions')->where('key', 'artigos.ver')->update(['label' => 'Ver Artigos']);
        DB::table('permissions')->where('key', 'artigos.criar')->update(['label' => 'Criar/editar em Artigos']);
    }
};
