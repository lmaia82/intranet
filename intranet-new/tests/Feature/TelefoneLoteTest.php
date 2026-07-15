<?php

namespace Tests\Feature;

use App\Models\Sector;
use App\Models\Telefone;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class TelefoneLoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_cadastro_em_lote_de_ramais(): void
    {
        $user = User::factory()->create();
        $sector = Sector::create(['name' => 'TI']);

        $csv = "nome,telefone,setor,email,cargo\n";
        $csv .= "Fulano de Tal,2222,TI,fulano@cetem.gov.br,Analista\n";
        $csv .= "Sem Setor,3333,Inexistente,semse@cetem.gov.br,Analista\n";
        $csv .= "Sem Telefone,,TI,,\n";

        $file = UploadedFile::fake()->createWithContent('ramais.csv', $csv);

        $response = $this->actingAs($user)->post(route('telefones.lote.import'), ['arquivo' => $file]);

        $response->assertRedirect(route('telefones.lote.form'));
        $this->assertDatabaseHas('telefones', ['nome' => 'Fulano de Tal', 'sector_id' => $sector->id]);
        $this->assertDatabaseMissing('telefones', ['nome' => 'Sem Setor']);
        $this->assertDatabaseMissing('telefones', ['nome' => 'Sem Telefone']);
        $this->assertEquals(1, Telefone::count());
    }

    public function test_cadastro_em_lote_com_colunas_do_catalogo_telefonico(): void
    {
        $user = User::factory()->create();
        $sector = Sector::create(['name' => 'COADM']);

        $csv = "Ramal ,Unidade ,Setor ,Nome ,Cargo ,E-mail ,Telefone Externo \n";
        $csv .= "1000,CETEM-RJ,COADM,Rosângela Bastos Torres,Secretária da COADM,rtorres@cetem.gov.br,(21)3512-9902\n";

        $file = UploadedFile::fake()->createWithContent('ramais.csv', $csv);

        $response = $this->actingAs($user)->post(route('telefones.lote.import'), ['arquivo' => $file]);

        $response->assertRedirect(route('telefones.lote.form'));
        $this->assertDatabaseHas('telefones', [
            'nome' => 'Rosângela Bastos Torres',
            'unidade' => 'CETEM-RJ',
            'telefone' => '1000',
            'sector_id' => $sector->id,
            'telefone_externo' => '(21)3512-9902',
        ]);
    }

    public function test_cadastro_em_lote_a_partir_de_arquivo_xlsx(): void
    {
        $user = User::factory()->create();
        $sector = Sector::create(['name' => 'COADM']);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['Ramal ', 'Unidade ', 'Setor ', 'Nome ', 'Cargo ', 'E-mail ', 'Telefone Externo '],
            ['1001', 'CETEM-RJ', 'COADM', 'Robson Araujo', 'Coordenador(a) da COADM', 'rdavila@cetem.gov.br', '(21)3512-9903'],
        ], null, 'A1');

        $caminhoTemporario = tempnam(sys_get_temp_dir(), 'ramais') . '.xlsx';
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($caminhoTemporario);

        $file = new UploadedFile($caminhoTemporario, 'catalogo.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->actingAs($user)->post(route('telefones.lote.import'), ['arquivo' => $file]);

        $response->assertRedirect(route('telefones.lote.form'));
        $this->assertDatabaseHas('telefones', [
            'nome' => 'Robson Araujo',
            'unidade' => 'CETEM-RJ',
            'telefone' => '1001',
            'sector_id' => $sector->id,
        ]);

        unlink($caminhoTemporario);
    }

    public function test_pagina_de_cadastro_em_lote_de_ramais_carrega(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('telefones.lote.form'))
            ->assertOk()
            ->assertSee('Baixar modelo CSV');
    }

    public function test_pagina_de_ramais_lista_indice_alfabetico(): void
    {
        $user = User::factory()->create();
        $sector = Sector::create(['name' => 'TI']);
        Telefone::create(['nome' => 'Ana Silva', 'telefone' => '1111', 'sector_id' => $sector->id]);
        Telefone::create(['nome' => 'Bruno Costa', 'telefone' => '2222', 'sector_id' => $sector->id]);

        $this->actingAs($user)->get(route('telefones.index'))
            ->assertOk()
            ->assertSee('Ana Silva')
            ->assertSee('Bruno Costa');
    }
}
