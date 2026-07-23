<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Só "ver" — a tela é somente leitura, montada a partir da hierarquia
     * já configurada em Admin > Setores, sem nada próprio para criar/editar.
     */
    public function up(): void {
        $now = now();

        $permissionId = DB::table('permissions')->insertGetId([
            'key' => 'organograma.ver',
            'label' => 'Ver Organograma',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $groupIds = DB::table('groups')->whereIn('name', ['Colaboradores', 'Leitores'])->pluck('id');

        DB::table('group_permission')->insert($groupIds->map(fn ($groupId) => [
            'group_id' => $groupId,
            'permission_id' => $permissionId,
        ])->all());
    }

    public function down(): void {
        DB::table('permissions')->where('key', 'organograma.ver')->delete();
    }
};
