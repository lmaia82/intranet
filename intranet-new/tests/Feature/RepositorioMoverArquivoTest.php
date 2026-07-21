<?php

namespace Tests\Feature;

use App\Models\Arquivo;
use App\Models\Pasta;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RepositorioMoverArquivoTest extends TestCase
{
    use RefreshDatabase;

    private function arquivoEmPasta(Pasta $pasta, Sector $sector): Arquivo
    {
        Storage::fake('arquivos');
        Storage::disk('arquivos')->put('uploads/nota.pdf', 'conteudo');

        return Arquivo::create([
            'pasta_id' => $pasta->id,
            'nome_original' => 'nota.pdf',
            'caminho' => 'uploads/nota.pdf',
            'extensao' => 'pdf',
            'tamanho' => 100,
            'sector_id' => $sector->id,
            'is_private' => false,
        ]);
    }

    public function test_formulario_de_mover_lista_pastas_visiveis(): void
    {
        $sector = Sector::create(['sigla' => 'TI']);
        $user = User::factory()->create(['sector_id' => $sector->id]);
        $origem = Pasta::create(['nome' => 'Origem', 'sector_id' => $sector->id, 'is_private' => false]);
        $destino = Pasta::create(['nome' => 'Destino', 'sector_id' => $sector->id, 'is_private' => false]);
        $arquivo = $this->arquivoEmPasta($origem, $sector);

        $this->actingAs($user)->get(route('repositorio.arquivos.mover.form', $arquivo))
            ->assertOk()
            ->assertSee('nota.pdf')
            ->assertSee('Origem')
            ->assertSee('Destino');
    }

    public function test_move_arquivo_para_outra_pasta(): void
    {
        $sector = Sector::create(['sigla' => 'TI']);
        $user = User::factory()->create(['sector_id' => $sector->id]);
        $origem = Pasta::create(['nome' => 'Origem', 'sector_id' => $sector->id, 'is_private' => false]);
        $destino = Pasta::create(['nome' => 'Destino', 'sector_id' => $sector->id, 'is_private' => false]);
        $arquivo = $this->arquivoEmPasta($origem, $sector);

        $response = $this->actingAs($user)->put(route('repositorio.arquivos.mover', $arquivo), [
            'pasta_id' => $destino->id,
        ]);

        $response->assertRedirect(route('repositorio.index', ['pasta' => $destino->id]));
        $this->assertEquals($destino->id, $arquivo->fresh()->pasta_id);
    }

    public function test_mover_para_pasta_sem_acesso_e_bloqueado(): void
    {
        $sectorA = Sector::create(['sigla' => 'TI']);
        $sectorB = Sector::create(['sigla' => 'RH']);
        $user = User::factory()->create(['sector_id' => $sectorA->id]);
        $origem = Pasta::create(['nome' => 'Origem', 'sector_id' => $sectorA->id, 'is_private' => false]);
        $restrita = Pasta::create(['nome' => 'Restrita', 'sector_id' => $sectorB->id, 'is_private' => true]);
        $arquivo = $this->arquivoEmPasta($origem, $sectorA);

        $response = $this->actingAs($user)->put(route('repositorio.arquivos.mover', $arquivo), [
            'pasta_id' => $restrita->id,
        ]);

        $response->assertForbidden();
        $this->assertEquals($origem->id, $arquivo->fresh()->pasta_id);
    }

    public function test_mover_arquivo_sem_acesso_ao_arquivo_e_bloqueado(): void
    {
        $sectorA = Sector::create(['sigla' => 'TI']);
        $sectorB = Sector::create(['sigla' => 'RH']);
        $user = User::factory()->create(['sector_id' => $sectorA->id]);
        $origemRestrita = Pasta::create(['nome' => 'Origem', 'sector_id' => $sectorB->id, 'is_private' => true]);
        $destino = Pasta::create(['nome' => 'Destino', 'sector_id' => $sectorA->id, 'is_private' => false]);
        $arquivo = $this->arquivoEmPasta($origemRestrita, $sectorB);
        $arquivo->update(['is_private' => true]);

        $this->actingAs($user)->get(route('repositorio.arquivos.mover.form', $arquivo))->assertForbidden();

        $response = $this->actingAs($user)->put(route('repositorio.arquivos.mover', $arquivo), [
            'pasta_id' => $destino->id,
        ]);
        $response->assertForbidden();
    }

    public function test_usuario_sem_permissao_nao_acessa_mover(): void
    {
        $sector = Sector::create(['sigla' => 'TI']);
        $user = User::factory()->create(['sector_id' => $sector->id, 'group_id' => null]);
        $origem = Pasta::create(['nome' => 'Origem', 'sector_id' => $sector->id, 'is_private' => false]);
        $arquivo = $this->arquivoEmPasta($origem, $sector);

        $this->actingAs($user)->get(route('repositorio.arquivos.mover.form', $arquivo))->assertForbidden();
    }
}
