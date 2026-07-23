<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Informativo;
use App\Models\Permission;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InformativoVisibilidadeTest extends TestCase
{
    use RefreshDatabase;

    private function usuarioComPermissao(?int $sectorId = null, bool $isAdmin = false): User
    {
        $permissao = Permission::where('key', 'informativos.ver')->first();
        $grupo = Group::create(['name' => 'Leitores Informativos ' . uniqid()]);
        $grupo->permissions()->attach($permissao);

        return User::factory()->create(['group_id' => $grupo->id, 'sector_id' => $sectorId, 'is_admin' => $isAdmin]);
    }

    public function test_informativo_publico_aparece_na_listagem_para_qualquer_usuario(): void
    {
        $user = $this->usuarioComPermissao();
        Informativo::create(['title' => 'Aviso Publico X', 'content' => 'x', 'is_private' => false, 'published_at' => now()]);

        $this->actingAs($user)->get(route('informativos.index'))->assertOk()->assertSee('Aviso Publico X');
    }

    public function test_informativo_privado_nao_aparece_na_listagem_para_usuario_de_outro_setor(): void
    {
        $setorA = Sector::create(['sigla' => 'SETA']);
        $setorB = Sector::create(['sigla' => 'SETB']);
        $user = $this->usuarioComPermissao($setorA->id);

        Informativo::create(['title' => 'Aviso Privado Y', 'content' => 'x', 'sector_id' => $setorB->id, 'is_private' => true, 'published_at' => now()]);

        $this->actingAs($user)->get(route('informativos.index'))->assertOk()->assertDontSee('Aviso Privado Y');
    }

    public function test_informativo_privado_aparece_na_listagem_para_usuario_do_mesmo_setor(): void
    {
        $setor = Sector::create(['sigla' => 'SETA']);
        $user = $this->usuarioComPermissao($setor->id);

        Informativo::create(['title' => 'Aviso Privado Z', 'content' => 'x', 'sector_id' => $setor->id, 'is_private' => true, 'published_at' => now()]);

        $this->actingAs($user)->get(route('informativos.index'))->assertOk()->assertSee('Aviso Privado Z');
    }

    public function test_informativo_privado_da_coordenacao_aparece_para_usuario_do_servico_subordinado(): void
    {
        $coordenacao = Sector::create(['sigla' => 'COADM']);
        $servico = Sector::create(['sigla' => 'SECOF', 'parent_id' => $coordenacao->id]);
        $user = $this->usuarioComPermissao($servico->id);

        Informativo::create(['title' => 'Aviso Da Coordenacao W', 'content' => 'x', 'sector_id' => $coordenacao->id, 'is_private' => true, 'published_at' => now()]);

        $this->actingAs($user)->get(route('informativos.index'))->assertOk()->assertSee('Aviso Da Coordenacao W');
    }

    public function test_informativo_privado_aparece_na_listagem_para_admin(): void
    {
        $setor = Sector::create(['sigla' => 'SETB']);
        $admin = $this->usuarioComPermissao(null, true);

        Informativo::create(['title' => 'Aviso Privado Admin', 'content' => 'x', 'sector_id' => $setor->id, 'is_private' => true, 'published_at' => now()]);

        $this->actingAs($admin)->get(route('informativos.index'))->assertOk()->assertSee('Aviso Privado Admin');
    }

    public function test_detalhe_de_informativo_privado_bloqueado_para_usuario_de_outro_setor(): void
    {
        $setorA = Sector::create(['sigla' => 'SETA']);
        $setorB = Sector::create(['sigla' => 'SETB']);
        $user = $this->usuarioComPermissao($setorA->id);

        $informativo = Informativo::create(['title' => 'Aviso Privado', 'content' => 'x', 'sector_id' => $setorB->id, 'is_private' => true, 'published_at' => now()]);

        $this->actingAs($user)->get(route('informativos.show', $informativo))->assertForbidden();
    }

    public function test_detalhe_de_informativo_privado_liberado_para_usuario_do_mesmo_setor(): void
    {
        $setor = Sector::create(['sigla' => 'SETA']);
        $user = $this->usuarioComPermissao($setor->id);

        $informativo = Informativo::create(['title' => 'Aviso Privado', 'content' => 'x', 'sector_id' => $setor->id, 'is_private' => true, 'published_at' => now()]);

        $this->actingAs($user)->get(route('informativos.show', $informativo))->assertOk();
    }

    public function test_detalhe_de_informativo_privado_da_coordenacao_liberado_para_servico_subordinado(): void
    {
        $coordenacao = Sector::create(['sigla' => 'COADM']);
        $servico = Sector::create(['sigla' => 'SECOF', 'parent_id' => $coordenacao->id]);
        $user = $this->usuarioComPermissao($servico->id);

        $informativo = Informativo::create(['title' => 'Aviso Da Coordenacao', 'content' => 'x', 'sector_id' => $coordenacao->id, 'is_private' => true, 'published_at' => now()]);

        $this->actingAs($user)->get(route('informativos.show', $informativo))->assertOk();
    }

    public function test_detalhe_de_informativo_privado_liberado_para_admin(): void
    {
        $setor = Sector::create(['sigla' => 'SETB']);
        $admin = $this->usuarioComPermissao(null, true);

        $informativo = Informativo::create(['title' => 'Aviso Privado', 'content' => 'x', 'sector_id' => $setor->id, 'is_private' => true, 'published_at' => now()]);

        $this->actingAs($admin)->get(route('informativos.show', $informativo))->assertOk();
    }

    public function test_detalhe_de_informativo_publico_liberado_para_qualquer_usuario(): void
    {
        $user = $this->usuarioComPermissao();

        $informativo = Informativo::create(['title' => 'Aviso Publico', 'content' => 'x', 'is_private' => false, 'published_at' => now()]);

        $this->actingAs($user)->get(route('informativos.show', $informativo))->assertOk();
    }
}
