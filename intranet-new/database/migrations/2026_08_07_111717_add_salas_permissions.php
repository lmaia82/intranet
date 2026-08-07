<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * "Reserva de Sala" — disponível para os grupos Leitores e Editores
     * (ou seja, todo colaborador com acesso à intranet pode ver e criar
     * reservas de sala).
     */
    public function up(): void {
        $now = now();

        $permissionIds = [
            DB::table('permissions')->insertGetId([
                'key' => 'salas.ver',
                'label' => 'Ver Reserva de Sala',
                'created_at' => $now,
                'updated_at' => $now,
            ]),
            DB::table('permissions')->insertGetId([
                'key' => 'salas.criar',
                'label' => 'Criar/editar em Reserva de Sala',
                'created_at' => $now,
                'updated_at' => $now,
            ]),
        ];

        $groupIds = DB::table('groups')->whereIn('name', ['Editores', 'Leitores', 'Colaboradores'])->pluck('id');

        foreach ($groupIds as $groupId) {
            foreach ($permissionIds as $permissionId) {
                DB::table('group_permission')->insert([
                    'group_id' => $groupId,
                    'permission_id' => $permissionId,
                ]);
            }
        }
    }

    public function down(): void {
        DB::table('permissions')->whereIn('key', ['salas.ver', 'salas.criar'])->delete();
    }
};
