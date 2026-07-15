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

class ArquivoLoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_pagina_de_cadastro_em_lote_de_arquivos_carrega(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('repositorio.arquivos.lote.form'))
            ->assertOk()
            ->assertSee('Baixar modelo CSV');
    }

    public function test_cadastro_em_lote_de_arquivos(): void
    {
        Storage::fake('arquivos');
        $user = User::factory()->create();
        $sector = Sector::create(['name' => 'Financeiro']);

        $csv = "arquivo,pasta,setor,data,publico,descricao\n";
        $csv .= "nota.pdf,Notas Fiscais/2024,Financeiro,31/12/2024,nao,Nota de exemplo\n";
        $csv .= "orfao.pdf,,Financeiro,,sim,\n";
        $csv .= "sem-arquivo.pdf,,Financeiro,,sim,\n";
        $csv .= "setor-invalido.pdf,,Inexistente,,sim,\n";

        $arquivoNota = UploadedFile::fake()->create('nota.pdf', 50);
        $arquivoOrfao = UploadedFile::fake()->create('orfao.pdf', 30);
        $arquivoSetorInvalido = UploadedFile::fake()->create('setor-invalido.pdf', 10);

        $file = UploadedFile::fake()->createWithContent('lote.csv', $csv);

        $response = $this->actingAs($user)->post(route('repositorio.arquivos.lote.import'), [
            'csv' => $file,
            'arquivos' => [$arquivoNota, $arquivoOrfao, $arquivoSetorInvalido],
        ]);

        $response->assertRedirect(route('repositorio.arquivos.lote.form'));

        $this->assertEquals(2, Arquivo::count());

        $nota = Arquivo::where('nome_original', 'nota.pdf')->first();
        $this->assertNotNull($nota);
        $this->assertTrue($nota->is_private);
        $this->assertEquals('2024-12-31', $nota->data->toDateString());
        $this->assertEquals($sector->id, $nota->sector_id);
        $this->assertNotNull($nota->pasta_id);

        $pastaAno = Pasta::find($nota->pasta_id);
        $this->assertEquals('2024', $pastaAno->nome);
        $this->assertEquals('Notas Fiscais', $pastaAno->parent->nome);

        $orfao = Arquivo::where('nome_original', 'orfao.pdf')->first();
        $this->assertNotNull($orfao);
        $this->assertFalse($orfao->is_private);
        $this->assertNull($orfao->pasta_id);
        $this->assertEquals(now()->toDateString(), $orfao->data->toDateString());

        $this->assertDatabaseMissing('arquivos', ['nome_original' => 'sem-arquivo.pdf']);
        $this->assertDatabaseMissing('arquivos', ['nome_original' => 'setor-invalido.pdf']);

        Storage::disk('arquivos')->assertExists($nota->caminho);
    }
}
