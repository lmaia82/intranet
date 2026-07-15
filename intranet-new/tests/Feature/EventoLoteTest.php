<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class EventoLoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_pagina_de_cadastro_em_lote_de_eventos_carrega(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('eventos.lote.form'))
            ->assertOk()
            ->assertSee('Baixar modelo CSV');
    }

    public function test_cadastro_em_lote_de_eventos(): void
    {
        $user = User::factory()->create();

        $csv = "title,local,dt_start,dt_end,tm_start,tm_end,informacoes\n";
        $csv .= "Evento em lote,Sala 1,15/03/2026,,09:00,10:00,Pauta de exemplo\n";
        $csv .= "Evento data invalida,Sala 2,data-invalida,,,,\n";
        $csv .= "Evento sem local,,15/03/2026,,,,\n";
        $csv .= "Evento fim antes do inicio,Sala 3,15/03/2026,10/03/2026,,,\n";

        $file = UploadedFile::fake()->createWithContent('eventos.csv', $csv);

        $response = $this->actingAs($user)->post(route('eventos.lote.import'), ['csv' => $file]);

        $response->assertRedirect(route('eventos.lote.form'));
        $this->assertDatabaseHas('eventos', ['title' => 'Evento em lote', 'local' => 'Sala 1']);
        $this->assertDatabaseMissing('eventos', ['title' => 'Evento data invalida']);
        $this->assertDatabaseMissing('eventos', ['title' => 'Evento sem local']);
        $this->assertDatabaseMissing('eventos', ['title' => 'Evento fim antes do inicio']);
        $this->assertEquals(1, Evento::count());
    }
}
