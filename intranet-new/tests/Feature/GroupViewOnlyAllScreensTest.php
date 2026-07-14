<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupViewOnlyAllScreensTest extends TestCase
{
    use RefreshDatabase;

    private function criarUsuarioLeitor(array $verKeys): User
    {
        $group = Group::create(['name' => 'Leitor-' . implode('-', $verKeys)]);
        $group->permissions()->sync(Permission::whereIn('key', $verKeys)->pluck('id'));

        return User::factory()->create(['group_id' => $group->id]);
    }

    public function test_leitor_de_ramais_acessa_index(): void
    {
        $user = $this->criarUsuarioLeitor(['ramais.ver']);
        $this->actingAs($user)->get(route('telefones.index'))->assertOk();
    }

    public function test_leitor_de_informativos_acessa_index(): void
    {
        $user = $this->criarUsuarioLeitor(['informativos.ver']);
        $this->actingAs($user)->get(route('informativos.index'))->assertOk();
    }

    public function test_leitor_de_eventos_acessa_index(): void
    {
        $user = $this->criarUsuarioLeitor(['eventos.ver']);
        $this->actingAs($user)->get(route('eventos.index'))->assertOk();
    }

    public function test_leitor_de_artigos_acessa_index(): void
    {
        $user = $this->criarUsuarioLeitor(['artigos.ver']);
        $this->actingAs($user)->get(route('artigos.index'))->assertOk();
    }

    public function test_leitor_de_repositorio_acessa_index(): void
    {
        $user = $this->criarUsuarioLeitor(['repositorio.ver']);
        $this->actingAs($user)->get(route('repositorio.index'))->assertOk();
    }

    public function test_leitor_de_tudo_acessa_todas_as_telas(): void
    {
        $user = $this->criarUsuarioLeitor([
            'ramais.ver', 'informativos.ver', 'eventos.ver', 'artigos.ver', 'repositorio.ver',
        ]);

        $this->actingAs($user)->get(route('telefones.index'))->assertOk();
        $this->actingAs($user)->get(route('informativos.index'))->assertOk();
        $this->actingAs($user)->get(route('eventos.index'))->assertOk();
        $this->actingAs($user)->get(route('artigos.index'))->assertOk();
        $this->actingAs($user)->get(route('repositorio.index'))->assertOk();

        // E continua sem poder criar em nenhuma delas.
        $this->actingAs($user)->get(route('telefones.create'))->assertForbidden();
        $this->actingAs($user)->get(route('informativos.create'))->assertForbidden();
        $this->actingAs($user)->get(route('eventos.create'))->assertForbidden();
        $this->actingAs($user)->get(route('artigos.create'))->assertForbidden();
    }
}
