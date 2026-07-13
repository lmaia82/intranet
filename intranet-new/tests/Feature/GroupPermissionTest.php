<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_sem_grupo_nao_acessa_telas_protegidas(): void
    {
        $user = User::factory()->create(['group_id' => null]);

        $this->actingAs($user)->get(route('informativos.index'))->assertForbidden();
    }

    public function test_usuario_com_permissao_de_ver_acessa_mas_nao_pode_criar(): void
    {
        $verPermission = Permission::where('key', 'informativos.ver')->first();
        $group = Group::create(['name' => 'Leitores']);
        $group->permissions()->attach($verPermission);

        $user = User::factory()->create(['group_id' => $group->id]);

        $this->actingAs($user)->get(route('informativos.index'))->assertOk();
        $this->actingAs($user)->get(route('informativos.create'))->assertForbidden();
        $this->actingAs($user)->post(route('informativos.store'), [
            'title' => 'x', 'content' => 'y',
        ])->assertForbidden();
    }

    public function test_usuario_com_permissao_de_criar_pode_publicar(): void
    {
        $criarPermission = Permission::where('key', 'informativos.criar')->first();
        $group = Group::create(['name' => 'Editores']);
        $group->permissions()->attach($criarPermission);

        $user = User::factory()->create(['group_id' => $group->id]);

        // Sem a permissão "ver", o índice continua bloqueado...
        $this->actingAs($user)->get(route('informativos.index'))->assertForbidden();
        // ...mas a criação funciona.
        $this->actingAs($user)->get(route('informativos.create'))->assertOk();
    }

    public function test_admin_ignora_restricoes_de_grupo(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'group_id' => null]);

        $this->actingAs($admin)->get(route('informativos.index'))->assertOk();
        $this->actingAs($admin)->get(route('informativos.create'))->assertOk();
    }
}
