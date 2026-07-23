<?php

namespace Tests\Feature;

use App\Models\Informativo;
use App\Models\Pasta;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SectorHierarquiaTest extends TestCase
{
    use RefreshDatabase;

    public function test_caminho_hierarquico_com_coordenacao_pai(): void
    {
        $coordenacao = Sector::create(['sigla' => 'COADM']);
        $servico = Sector::create(['sigla' => 'SECOF', 'parent_id' => $coordenacao->id]);

        $this->assertSame('COADM / SECOF', $servico->caminhoHierarquico());
    }

    public function test_caminho_hierarquico_sem_coordenacao_pai(): void
    {
        $coordenacao = Sector::create(['sigla' => 'COADM']);

        $this->assertSame('COADM', $coordenacao->caminhoHierarquico());
    }

    public function test_pasta_raiz_do_servico_fica_dentro_da_pasta_raiz_da_coordenacao(): void
    {
        $coordenacao = Sector::create(['sigla' => 'COADM']);
        $servico = Sector::create(['sigla' => 'SECOF', 'parent_id' => $coordenacao->id]);

        $temporariaServico = $servico->pastaTemporaria();
        $raizServico = Pasta::find($temporariaServico->parent_id);

        $this->assertSame('SECOF', $raizServico->nome);
        $this->assertNotNull($raizServico->parent_id);

        $raizCoordenacao = Pasta::find($raizServico->parent_id);
        $this->assertSame('COADM', $raizCoordenacao->nome);
        $this->assertNull($raizCoordenacao->parent_id);
    }

    public function test_pasta_raiz_da_coordenacao_continua_no_topo_quando_nao_tem_pai(): void
    {
        $coordenacao = Sector::create(['sigla' => 'COADM']);

        $temporaria = $coordenacao->pastaTemporaria();
        $raiz = Pasta::find($temporaria->parent_id);

        $this->assertSame('COADM', $raiz->nome);
        $this->assertNull($raiz->parent_id);
    }

    public function test_dashboard_mostra_coordenacao_e_servico_da_lotacao(): void
    {
        $coordenacao = Sector::create(['sigla' => 'COADM', 'nome' => 'Coordenação de Administração']);
        $servico = Sector::create(['sigla' => 'SECOF', 'nome' => 'Serviço de Contabilidade', 'parent_id' => $coordenacao->id]);
        $user = User::factory()->create(['sector_id' => $servico->id]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('(COADM / SECOF)', false);
    }

    public function test_informativo_mostra_coordenacao_e_servico_do_setor(): void
    {
        $coordenacao = Sector::create(['sigla' => 'COADM']);
        $servico = Sector::create(['sigla' => 'SECOF', 'parent_id' => $coordenacao->id]);
        $user = User::factory()->create();
        $informativo = Informativo::create([
            'title' => 'Aviso do SECOF',
            'content' => 'x',
            'sector_id' => $servico->id,
            'published_at' => now(),
            'is_private' => false,
        ]);

        $this->actingAs($user)->get(route('informativos.show', $informativo))
            ->assertOk()
            ->assertSee('COADM / SECOF');
    }

    public function test_admin_define_coordenacao_de_um_setor(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $coordenacao = Sector::create(['sigla' => 'COADM']);
        $servico = Sector::create(['sigla' => 'SECOF']);

        $this->actingAs($admin)->put(route('admin.setores.update', $servico), [
            'sigla' => 'SECOF',
            'parent_id' => $coordenacao->id,
        ])->assertRedirect(route('admin.setores'));

        $this->assertSame($coordenacao->id, $servico->fresh()->parent_id);
    }

    public function test_setor_nao_pode_ser_seu_proprio_pai(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $setor = Sector::create(['sigla' => 'COADM']);

        $this->actingAs($admin)->put(route('admin.setores.update', $setor), [
            'sigla' => 'COADM',
            'parent_id' => $setor->id,
        ])->assertSessionHasErrors('parent_id');

        $this->assertNull($setor->fresh()->parent_id);
    }
}
