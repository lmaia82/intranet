<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        $now = now();

        $groupId = DB::table('groups')->insertGetId([
            'name' => 'Leitor',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Permissão mínima: só "ver", em todas as telas que já existem hoje.
        // Grupo padrão para quem entra pela primeira vez via AD — ver
        // App\Services\ActiveDirectoryAuthenticator.
        $permissionIds = DB::table('permissions')->where('key', 'like', '%.ver')->pluck('id');

        DB::table('group_permission')->insert($permissionIds->map(fn ($permissionId) => [
            'group_id' => $groupId,
            'permission_id' => $permissionId,
        ])->all());
    }

    public function down(): void {
        DB::table('groups')->where('name', 'Leitor')->delete();
    }
};
