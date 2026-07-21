<?php

namespace Tests\Feature;

use App\Models\Arquivo;
use App\Models\Pasta;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnlyOfficeSetorTemporarioTest extends TestCase
{
    use RefreshDatabase;

    public function test_pasta_temporaria_cria_raiz_do_setor_e_subpasta_restrita(): void
    {
        $sector = Sector::create(['sigla' => 'TI']);

        $temporaria = $sector->pastaTemporaria();

        $this->assertEquals('Temporário', $temporaria->nome);
        $this->assertTrue($temporaria->is_private);
        $this->assertEquals($sector->id, $temporaria->sector_id);

        $raiz = Pasta::find($temporaria->parent_id);
        $this->assertEquals('TI', $raiz->nome);
        $this->assertFalse($raiz->is_private);
        $this->assertEquals($sector->id, $raiz->sector_id);
    }

    public function test_pasta_temporaria_e_idempotente(): void
    {
        $sector = Sector::create(['sigla' => 'TI']);

        $primeira = $sector->pastaTemporaria();
        $segunda = $sector->pastaTemporaria();

        $this->assertEquals($primeira->id, $segunda->id);
        $this->assertEquals(2, Pasta::count());
    }

    public function test_criar_documento_coloca_na_pasta_temporaria_do_setor_do_usuario(): void
    {
        $sector = Sector::create(['sigla' => 'TI']);
        $user = User::factory()->create(['sector_id' => $sector->id]);

        $this->actingAs($user)->post(route('onlyoffice.criar'), [
            'tipo' => 'docx',
            'titulo' => 'Relatorio',
        ]);

        $arquivo = Arquivo::where('nome_original', 'Relatorio.docx')->firstOrFail();
        $temporaria = $sector->pastaTemporaria();

        $this->assertEquals($temporaria->id, $arquivo->pasta_id);
        $this->assertTrue($arquivo->is_private);
        $this->assertEquals($sector->id, $arquivo->sector_id);
    }

    public function test_usuario_sem_setor_nao_consegue_criar_documento(): void
    {
        $user = User::factory()->create(['sector_id' => null]);

        $response = $this->actingAs($user)->post(route('onlyoffice.criar'), [
            'tipo' => 'docx',
            'titulo' => 'Relatorio',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('arquivos', 0);
    }

    public function test_usuario_do_mesmo_setor_abre_editor_do_documento(): void
    {
        $sector = Sector::create(['sigla' => 'TI']);
        $criador = User::factory()->create(['sector_id' => $sector->id]);
        $colega = User::factory()->create(['sector_id' => $sector->id]);

        $this->actingAs($criador)->post(route('onlyoffice.criar'), [
            'tipo' => 'docx',
            'titulo' => 'Relatorio',
        ]);
        $arquivo = Arquivo::where('nome_original', 'Relatorio.docx')->firstOrFail();

        $this->actingAs($colega)->get(route('onlyoffice.editor', $arquivo))->assertOk();
    }

    public function test_usuario_de_outro_setor_nao_abre_editor_do_documento(): void
    {
        $sectorA = Sector::create(['sigla' => 'TI']);
        $sectorB = Sector::create(['sigla' => 'RH']);
        $criador = User::factory()->create(['sector_id' => $sectorA->id]);
        $outro = User::factory()->create(['sector_id' => $sectorB->id]);

        $this->actingAs($criador)->post(route('onlyoffice.criar'), [
            'tipo' => 'docx',
            'titulo' => 'Relatorio',
        ]);
        $arquivo = Arquivo::where('nome_original', 'Relatorio.docx')->firstOrFail();

        $this->actingAs($outro)->get(route('onlyoffice.editor', $arquivo))->assertForbidden();
    }

    public function test_admin_abre_editor_de_documento_de_qualquer_setor(): void
    {
        $sector = Sector::create(['sigla' => 'TI']);
        $criador = User::factory()->create(['sector_id' => $sector->id]);
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($criador)->post(route('onlyoffice.criar'), [
            'tipo' => 'docx',
            'titulo' => 'Relatorio',
        ]);
        $arquivo = Arquivo::where('nome_original', 'Relatorio.docx')->firstOrFail();

        $this->actingAs($admin)->get(route('onlyoffice.editor', $arquivo))->assertOk();
    }

    public function test_pagina_de_aplicacoes_renderiza_sem_secao_de_meus_documentos(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('onlyoffice.aplicacoes'))
            ->assertOk()
            ->assertDontSee('Meus documentos');
    }
}
