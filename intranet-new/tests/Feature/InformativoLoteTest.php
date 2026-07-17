<?php

namespace Tests\Feature;

use App\Models\Informativo;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class InformativoLoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_pagina_de_cadastro_em_lote_carrega(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('informativos.lote.form'))
            ->assertOk()
            ->assertSee('Baixar modelo CSV');
    }

    public function test_cadastro_em_lote_de_informativos(): void
    {
        $user = User::factory()->create();
        $sector = Sector::create(['sigla' => 'TI']);

        $csv = "title,content,setor,publico,data_publicacao\n";
        $csv .= "Aviso em lote,Conteudo simples,TI,nao,15/03/2026\n";
        $csv .= "Sem conteudo,,TI,sim,\n";
        $csv .= "Setor invalido,Texto,Inexistente,sim,\n";
        $csv .= "Data invalida,Texto,,sim,data-ruim\n";

        $file = UploadedFile::fake()->createWithContent('informativos.csv', $csv);

        $response = $this->actingAs($user)->post(route('informativos.lote.import'), ['csv' => $file]);

        $response->assertRedirect(route('informativos.lote.form'));

        $this->assertDatabaseHas('informativos', [
            'title' => 'Aviso em lote',
            'sector_id' => $sector->id,
            'is_private' => true,
        ]);
        $this->assertDatabaseMissing('informativos', ['title' => 'Sem conteudo']);
        $this->assertDatabaseMissing('informativos', ['title' => 'Setor invalido']);
        $this->assertDatabaseMissing('informativos', ['title' => 'Data invalida']);
        $this->assertEquals(1, Informativo::count());
    }

    public function test_conteudo_com_quebra_de_linha_entre_aspas_e_importado_corretamente(): void
    {
        $user = User::factory()->create();

        $csv = "title,content,setor,publico,data_publicacao\n";
        $csv .= "Informativo multilinha,\"Primeira linha.\nSegunda linha.\nTerceira linha.\",,sim,\n";

        $file = UploadedFile::fake()->createWithContent('informativos.csv', $csv);

        $this->actingAs($user)->post(route('informativos.lote.import'), ['csv' => $file])
            ->assertRedirect(route('informativos.lote.form'));

        $informativo = Informativo::where('title', 'Informativo multilinha')->first();
        $this->assertNotNull($informativo);
        $this->assertEquals("Primeira linha.\nSegunda linha.\nTerceira linha.", $informativo->content);
    }

    public function test_data_em_branco_usa_data_de_hoje(): void
    {
        $user = User::factory()->create();

        $csv = "title,content,setor,publico,data_publicacao\n";
        $csv .= "Sem data,Conteudo,,sim,\n";

        $file = UploadedFile::fake()->createWithContent('informativos.csv', $csv);

        $this->actingAs($user)->post(route('informativos.lote.import'), ['csv' => $file]);

        $informativo = Informativo::where('title', 'Sem data')->first();
        $this->assertNotNull($informativo);
        $this->assertEquals(now()->toDateString(), $informativo->published_at->toDateString());
    }
}
