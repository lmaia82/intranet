<?php

namespace Tests\Feature;

use App\Models\Arquivo;
use App\Models\Pasta;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RepositorioCriadoPorEDestinoTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_registra_o_usuario_que_criou_o_arquivo(): void
    {
        Storage::fake('arquivos');
        $sector = Sector::create(['sigla' => 'TI']);
        $user = User::factory()->create(['sector_id' => $sector->id, 'email' => 'quemenviou@cetem.gov.br']);
        $pasta = Pasta::create(['nome' => 'Notas Fiscais', 'sector_id' => $sector->id, 'is_private' => false]);

        $this->actingAs($user)->post(route('repositorio.arquivos.store'), [
            'arquivo' => UploadedFile::fake()->create('nota.pdf', 10),
            'sector_id' => $sector->id,
            'pasta_id' => $pasta->id,
        ]);

        $arquivo = Arquivo::where('nome_original', 'nota.pdf')->firstOrFail();
        $this->assertEquals($user->id, $arquivo->criado_por_id);
    }

    public function test_email_do_criador_aparece_na_listagem_do_repositorio(): void
    {
        Storage::fake('arquivos');
        $sector = Sector::create(['sigla' => 'TI']);
        $user = User::factory()->create(['sector_id' => $sector->id, 'email' => 'quemenviou@cetem.gov.br']);
        $pasta = Pasta::create(['nome' => 'Notas Fiscais', 'sector_id' => $sector->id, 'is_private' => false]);

        $this->actingAs($user)->post(route('repositorio.arquivos.store'), [
            'arquivo' => UploadedFile::fake()->create('nota.pdf', 10),
            'sector_id' => $sector->id,
            'pasta_id' => $pasta->id,
        ]);

        $response = $this->actingAs($user)->get(route('repositorio.index', ['pasta' => $pasta->id]));

        $response->assertOk()->assertSee('quemenviou@cetem.gov.br');
    }

    public function test_upload_sem_pasta_e_bloqueado(): void
    {
        Storage::fake('arquivos');
        $sector = Sector::create(['sigla' => 'TI']);
        $user = User::factory()->create(['sector_id' => $sector->id]);

        $response = $this->actingAs($user)->post(route('repositorio.arquivos.store'), [
            'arquivo' => UploadedFile::fake()->create('nota.pdf', 10),
            'sector_id' => $sector->id,
        ]);

        $response->assertSessionHasErrors('pasta_id');
        $this->assertDatabaseCount('arquivos', 0);
    }

    public function test_documento_criado_pelas_aplicacoes_tambem_registra_criador(): void
    {
        $sector = Sector::create(['sigla' => 'TI']);
        $user = User::factory()->create(['sector_id' => $sector->id]);

        $this->actingAs($user)->post(route('onlyoffice.criar'), [
            'tipo' => 'docx',
            'titulo' => 'DocTeste',
        ]);

        $arquivo = Arquivo::where('nome_original', 'DocTeste.docx')->firstOrFail();
        $this->assertEquals($user->id, $arquivo->criado_por_id);
    }

    public function test_formulario_de_envio_lista_pastas_visiveis_para_selecao(): void
    {
        $sector = Sector::create(['sigla' => 'TI']);
        $user = User::factory()->create(['sector_id' => $sector->id, 'is_admin' => true]);
        $pasta = Pasta::create(['nome' => 'Notas Fiscais', 'sector_id' => $sector->id, 'is_private' => false]);
        $subpasta = Pasta::create(['nome' => '2024', 'parent_id' => $pasta->id, 'sector_id' => $sector->id, 'is_private' => false]);

        $response = $this->actingAs($user)->get(route('repositorio.index'));

        $response->assertOk()
            ->assertSee('Pasta de destino')
            ->assertSee('Notas Fiscais / 2024');
    }

    public function test_upload_para_pasta_escolhida_no_select_vai_para_ela(): void
    {
        Storage::fake('arquivos');
        $sector = Sector::create(['sigla' => 'TI']);
        $user = User::factory()->create(['sector_id' => $sector->id]);
        $pasta = Pasta::create(['nome' => 'Notas Fiscais', 'sector_id' => $sector->id, 'is_private' => false]);

        $this->actingAs($user)->post(route('repositorio.arquivos.store'), [
            'arquivo' => UploadedFile::fake()->create('nota.pdf', 10),
            'sector_id' => $sector->id,
            'pasta_id' => $pasta->id,
        ]);

        $arquivo = Arquivo::where('nome_original', 'nota.pdf')->firstOrFail();
        $this->assertEquals($pasta->id, $arquivo->pasta_id);
    }

    public function test_upload_para_pasta_sem_acesso_e_bloqueado(): void
    {
        Storage::fake('arquivos');
        $sectorA = Sector::create(['sigla' => 'TI']);
        $sectorB = Sector::create(['sigla' => 'RH']);
        $user = User::factory()->create(['sector_id' => $sectorA->id]);
        $pastaRestrita = Pasta::create(['nome' => 'RH Confidencial', 'sector_id' => $sectorB->id, 'is_private' => true]);

        $response = $this->actingAs($user)->post(route('repositorio.arquivos.store'), [
            'arquivo' => UploadedFile::fake()->create('nota.pdf', 10),
            'sector_id' => $sectorA->id,
            'pasta_id' => $pastaRestrita->id,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('arquivos', 0);
    }
}
