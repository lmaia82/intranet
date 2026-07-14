<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        $now = now();

        $permissionIds = [
            DB::table('permissions')->insertGetId([
                'key' => 'destaques.ver',
                'label' => 'Ver Destaques',
                'created_at' => $now,
                'updated_at' => $now,
            ]),
            DB::table('permissions')->insertGetId([
                'key' => 'destaques.criar',
                'label' => 'Criar/editar em Destaques',
                'created_at' => $now,
                'updated_at' => $now,
            ]),
        ];

        $groupId = DB::table('groups')->where('name', 'Colaboradores')->value('id');

        if ($groupId) {
            DB::table('group_permission')->insert(array_map(fn ($permissionId) => [
                'group_id' => $groupId,
                'permission_id' => $permissionId,
            ], $permissionIds));
        }
    }

    public function down(): void {
        DB::table('permissions')->whereIn('key', ['destaques.ver', 'destaques.criar'])->delete();
    }
};
