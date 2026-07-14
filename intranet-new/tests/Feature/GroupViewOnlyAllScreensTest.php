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

    public function test_leitor_de_repositorio_acessa_index(): void
    {
        $user = $this->criarUsuarioLeitor(['repositorio.ver']);
        $this->actingAs($user)->get(route('repositorio.index'))->assertOk();
    }

    public function test_leitor_de_tudo_acessa_todas_as_telas(): void
    {
        $user = $this->criarUsuarioLeitor([
            'ramais.ver', 'informativos.ver', 'eventos.ver', 'repositorio.ver',
        ]);

        $this->actingAs($user)->get(route('telefones.index'))->assertOk();
        $this->actingAs($user)->get(route('informativos.index'))->assertOk();
        $this->actingAs($user)->get(route('eventos.index'))->assertOk();
        $this->actingAs($user)->get(route('repositorio.index'))->assertOk();

        // E continua sem poder criar em nenhuma delas.
        $this->actingAs($user)->get(route('telefones.create'))->assertForbidden();
        $this->actingAs($user)->get(route('informativos.create'))->assertForbidden();
        $this->actingAs($user)->get(route('eventos.create'))->assertForbidden();
    }

    public function test_leitor_nao_ve_botoes_de_criar_editar_remover(): void
    {
        $user = $this->criarUsuarioLeitor([
            'ramais.ver', 'informativos.ver', 'eventos.ver', 'repositorio.ver',
        ]);

        \App\Models\Telefone::create([
            'nome' => 'Fulano', 'telefone' => '1234',
            'sector_id' => \App\Models\Sector::create(['name' => 'TI'])->id,
        ]);
        \App\Models\Informativo::create(['title' => 'Aviso', 'content' => 'x', 'published_at' => now()]);

        $this->actingAs($user)->get(route('telefones.index'))
            ->assertOk()->assertDontSee('Novo ramal')->assertDontSee('Cadastro em lote')->assertDontSee('Remover');

        $this->actingAs($user)->get(route('informativos.index'))
            ->assertOk()->assertDontSee('Novo informativo')->assertDontSee('Editar')->assertDontSee('Remover');

        $this->actingAs($user)->get(route('eventos.index'))
            ->assertOk()->assertDontSee('Novo evento')->assertDontSee('Novo evento gravado');

        $this->actingAs($user)->get(route('repositorio.index'))
            ->assertOk()->assertDontSee('Nova pasta')->assertDontSee('Enviar arquivo');
    }

    public function test_artigos_e_acessivel_a_qualquer_usuario_autenticado_e_mostra_links_mineralis_e_master(): void
    {
        $user = $this->criarUsuarioLeitor([]);

        $this->actingAs($user)->get(route('artigos.index'))
            ->assertOk()
            ->assertSee('Mineralis')
            ->assertSee('https://mineralis.cetem.gov.br/buscar', false)
            ->assertSee('Master')
            ->assertSee('https://master.cetem.gov.br/', false);
    }

    public function test_grupo_leitor_de_informativos_nao_ve_botao_de_reenviar(): void
    {
        $user = $this->criarUsuarioLeitor(['informativos.ver']);
        $informativo = \App\Models\Informativo::create(['title' => 'Aviso', 'content' => 'x', 'published_at' => now()]);

        $this->actingAs($user)->get(route('informativos.show', $informativo))
            ->assertOk()->assertDontSee('Reenviar e-mails');
    }
}
