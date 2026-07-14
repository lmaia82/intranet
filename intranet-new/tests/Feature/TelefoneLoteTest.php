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

        $response = $this->actingAs($user)->post(route('telefones.lote.import'), ['csv' => $file]);

        $response->assertRedirect(route('telefones.lote.form'));
        $this->assertDatabaseHas('telefones', ['nome' => 'Fulano de Tal', 'sector_id' => $sector->id]);
        $this->assertDatabaseMissing('telefones', ['nome' => 'Sem Setor']);
        $this->assertDatabaseMissing('telefones', ['nome' => 'Sem Telefone']);
        $this->assertEquals(1, Telefone::count());
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
