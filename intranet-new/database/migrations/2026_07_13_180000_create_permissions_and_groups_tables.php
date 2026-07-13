<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->timestamps();
        });

        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('group_permission', function (Blueprint $table) {
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->primary(['group_id', 'permission_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('group_id')->nullable()->after('sector_id')->constrained('groups')->nullOnDelete();
        });

        $this->seedDefaultGroupAndPermissions();
    }

    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('group_id');
        });
        Schema::dropIfExists('group_permission');
        Schema::dropIfExists('groups');
        Schema::dropIfExists('permissions');
    }

    private function seedDefaultGroupAndPermissions(): void
    {
        $telas = ['ramais', 'informativos', 'eventos', 'artigos', 'repositorio'];
        $labels = [
            'ramais' => 'Ramais',
            'informativos' => 'Informativos',
            'eventos' => 'Agenda / Eventos',
            'artigos' => 'Artigos',
            'repositorio' => 'Repositório',
        ];

        $now = now();
        $permissionIds = [];

        foreach ($telas as $tela) {
            $permissionIds[] = DB::table('permissions')->insertGetId([
                'key' => "{$tela}.ver",
                'label' => "Ver {$labels[$tela]}",
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $permissionIds[] = DB::table('permissions')->insertGetId([
                'key' => "{$tela}.criar",
                'label' => "Criar/editar em {$labels[$tela]}",
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $groupId = DB::table('groups')->insertGetId([
            'name' => 'Colaboradores',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('group_permission')->insert(array_map(fn ($permissionId) => [
            'group_id' => $groupId,
            'permission_id' => $permissionId,
        ], $permissionIds));

        DB::table('users')->whereNull('group_id')->update(['group_id' => $groupId]);
    }
};
