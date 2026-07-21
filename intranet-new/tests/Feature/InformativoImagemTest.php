<?php

namespace Tests\Feature;

use App\Models\Informativo;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InformativoImagemTest extends TestCase
{
    use RefreshDatabase;

    public function test_imagem_do_informativo_e_salva_na_pasta_do_setor_do_usuario(): void
    {
        Storage::fake('arquivos');
        $sector = Sector::create(['sigla' => 'TI']);
        $user = User::factory()->create(['sector_id' => $sector->id]);

        $this->actingAs($user)->post(route('informativos.store'), [
            'title' => 'Aviso importante',
            'content' => 'Conteúdo do aviso.',
            'image' => UploadedFile::fake()->image('capa.png'),
        ])->assertRedirect(route('informativos.index'));

        $informativo = Informativo::where('title', 'Aviso importante')->firstOrFail();
        $this->assertNotNull($informativo->arquivo_id);
        $this->assertEquals($sector->id, $informativo->arquivo->sector_id);
        $this->assertEquals('Imagens Informativos', $informativo->arquivo->pasta->nome);
        $this->assertFalse($informativo->arquivo->is_private);
        Storage::disk('arquivos')->assertExists($informativo->arquivo->caminho);
    }

    public function test_informativo_sem_imagem_nao_exige_setor(): void
    {
        $user = User::factory()->create(['sector_id' => null]);

        $this->actingAs($user)->post(route('informativos.store'), [
            'title' => 'Aviso sem imagem',
            'content' => 'Conteúdo.',
        ])->assertRedirect(route('informativos.index'));

        $this->assertDatabaseHas('informativos', ['title' => 'Aviso sem imagem']);
    }

    public function test_upload_de_imagem_sem_setor_falha(): void
    {
        $user = User::factory()->create(['sector_id' => null]);

        $response = $this->actingAs($user)->post(route('informativos.store'), [
            'title' => 'Aviso com imagem',
            'content' => 'Conteúdo.',
            'image' => UploadedFile::fake()->image('capa.png'),
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('informativos', ['title' => 'Aviso com imagem']);
    }

    public function test_imagem_url_usa_rota_do_repositorio_quando_ha_arquivo(): void
    {
        Storage::fake('arquivos');
        $sector = Sector::create(['sigla' => 'TI']);
        $user = User::factory()->create(['sector_id' => $sector->id]);

        $this->actingAs($user)->post(route('informativos.store'), [
            'title' => 'Aviso com imagem',
            'content' => 'Conteúdo.',
            'image' => UploadedFile::fake()->image('capa.png'),
        ]);

        $informativo = Informativo::where('title', 'Aviso com imagem')->firstOrFail();

        $this->assertEquals(
            route('repositorio.arquivos.visualizar', $informativo->arquivo_id),
            $informativo->imagemUrl()
        );
    }

    public function test_imagem_url_cai_no_caminho_antigo_para_registros_legados(): void
    {
        $informativo = Informativo::create([
            'title' => 'Antigo',
            'content' => 'Conteúdo antigo.',
            'image' => 'informativos/antigo.jpg',
        ]);

        $this->assertStringContainsString('informativos/antigo.jpg', $informativo->imagemUrl());
    }

    public function test_excluir_informativo_remove_a_imagem_do_minio(): void
    {
        Storage::fake('arquivos');
        $sector = Sector::create(['sigla' => 'TI']);
        $user = User::factory()->create(['sector_id' => $sector->id]);

        $this->actingAs($user)->post(route('informativos.store'), [
            'title' => 'Aviso com imagem',
            'content' => 'Conteúdo.',
            'image' => UploadedFile::fake()->image('capa.png'),
        ]);

        $informativo = Informativo::where('title', 'Aviso com imagem')->firstOrFail();
        $caminho = $informativo->arquivo->caminho;
        $arquivoId = $informativo->arquivo_id;

        $this->actingAs($user)->delete(route('informativos.destroy', $informativo));

        $this->assertDatabaseMissing('arquivos', ['id' => $arquivoId]);
        Storage::disk('arquivos')->assertMissing($caminho);
    }

    public function test_trocar_imagem_na_edicao_remove_a_imagem_anterior_do_minio(): void
    {
        Storage::fake('arquivos');
        $sector = Sector::create(['sigla' => 'TI']);
        $user = User::factory()->create(['sector_id' => $sector->id]);

        $this->actingAs($user)->post(route('informativos.store'), [
            'title' => 'Aviso com imagem',
            'content' => 'Conteúdo.',
            'image' => UploadedFile::fake()->image('capa.png'),
        ]);

        $informativo = Informativo::where('title', 'Aviso com imagem')->firstOrFail();
        $caminhoAntigo = $informativo->arquivo->caminho;
        $arquivoIdAntigo = $informativo->arquivo_id;

        $this->actingAs($user)->put(route('informativos.update', $informativo), [
            'title' => 'Aviso com imagem',
            'content' => 'Conteúdo.',
            'image' => UploadedFile::fake()->image('nova.png'),
        ]);

        $informativo->refresh();
        $this->assertNotEquals($arquivoIdAntigo, $informativo->arquivo_id);
        $this->assertDatabaseMissing('arquivos', ['id' => $arquivoIdAntigo]);
        Storage::disk('arquivos')->assertMissing($caminhoAntigo);
        Storage::disk('arquivos')->assertExists($informativo->arquivo->caminho);
    }

    public function test_outro_usuario_do_mesmo_setor_visualiza_a_imagem(): void
    {
        Storage::fake('arquivos');
        $sector = Sector::create(['sigla' => 'TI']);
        $autor = User::factory()->create(['sector_id' => $sector->id]);
        $colega = User::factory()->create(['sector_id' => $sector->id]);

        $this->actingAs($autor)->post(route('informativos.store'), [
            'title' => 'Aviso',
            'content' => 'Conteúdo.',
            'image' => UploadedFile::fake()->image('capa.png'),
        ]);

        $informativo = Informativo::where('title', 'Aviso')->firstOrFail();

        $this->actingAs($colega)
            ->get(route('repositorio.arquivos.visualizar', $informativo->arquivo_id))
            ->assertOk();
    }
}
