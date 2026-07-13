<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminGroupTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pode_criar_grupo_com_permissoes(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $permissionIds = Permission::whereIn('key', ['informativos.ver', 'informativos.criar'])->pluck('id');

        $this->actingAs($admin)->post(route('admin.grupos.store'), [
            'name' => 'Redação',
            'permissions' => $permissionIds->all(),
        ])->assertRedirect(route('admin.grupos'));

        $grupo = Group::where('name', 'Redação')->firstOrFail();
        $this->assertEquals(2, $grupo->permissions()->count());
    }

    public function test_admin_pode_editar_permissoes_do_grupo(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $grupo = Group::create(['name' => 'Leitores']);
        $grupo->permissions()->attach(Permission::where('key', 'informativos.ver')->first());

        $novaPermissao = Permission::where('key', 'ramais.ver')->first();

        $this->actingAs($admin)->put(route('admin.grupos.update', $grupo), [
            'name' => 'Leitores',
            'permissions' => [$novaPermissao->id],
        ])->assertRedirect(route('admin.grupos'));

        $grupo->refresh();
        $this->assertEquals([$novaPermissao->id], $grupo->permissions->pluck('id')->all());
    }

    public function test_remover_grupo_desassocia_usuarios(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $grupo = Group::create(['name' => 'Temporário']);
        $usuario = User::factory()->create(['group_id' => $grupo->id]);

        $this->actingAs($admin)->delete(route('admin.grupos.destroy', $grupo))
            ->assertRedirect(route('admin.grupos'));

        $this->assertNull($usuario->fresh()->group_id);
    }

    public function test_usuario_nao_admin_nao_acessa_gestao_de_grupos(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get(route('admin.grupos'))->assertForbidden();
    }
}
