<?php

namespace Tests\Feature;

use App\Models\Acesso;
use App\Models\Group;
use App\Models\Informativo;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrarAcessoTest extends TestCase
{
    use RefreshDatabase;

    public function test_registra_acesso_ao_visitar_um_modulo_com_permissao(): void
    {
        $permissao = Permission::where('key', 'ramais.ver')->first();
        $grupo = Group::create(['name' => 'Leitor de Ramais']);
        $grupo->permissions()->attach($permissao);
        $user = User::factory()->create(['group_id' => $grupo->id]);

        $this->actingAs($user)->get(route('telefones.index'))->assertOk();

        $this->assertDatabaseHas('acessos', ['user_id' => $user->id, 'modulo' => 'ramais']);
        $this->assertEquals(1, Acesso::count());
    }

    public function test_nao_registra_acesso_quando_usuario_nao_tem_permissao(): void
    {
        $user = User::factory()->create(['group_id' => null]);

        $this->actingAs($user)->get(route('telefones.index'))->assertForbidden();

        $this->assertEquals(0, Acesso::count());
    }

    public function test_registra_acesso_ao_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('dashboard'))->assertOk();

        $this->assertDatabaseHas('acessos', ['user_id' => $user->id, 'modulo' => 'dashboard']);
    }

    public function test_registra_acesso_ao_repositorio(): void
    {
        $permissao = Permission::where('key', 'repositorio.ver')->first();
        $grupo = Group::create(['name' => 'Leitor de Repositorio']);
        $grupo->permissions()->attach($permissao);
        $user = User::factory()->create(['group_id' => $grupo->id]);

        $this->actingAs($user)->get(route('repositorio.index'))->assertOk();

        $this->assertDatabaseHas('acessos', ['user_id' => $user->id, 'modulo' => 'repositorio']);
    }

    public function test_informativos_show_nao_registra_acesso_de_modulo(): void
    {
        $permissao = Permission::where('key', 'informativos.ver')->first();
        $grupo = Group::create(['name' => 'Leitor de Informativos']);
        $grupo->permissions()->attach($permissao);
        $user = User::factory()->create(['group_id' => $grupo->id]);
        $informativo = Informativo::create(['title' => 'Aviso', 'content' => 'x', 'published_at' => now()]);

        $this->actingAs($user)->get(route('informativos.show', $informativo))->assertOk();

        $this->assertEquals(0, Acesso::count());
    }
}
