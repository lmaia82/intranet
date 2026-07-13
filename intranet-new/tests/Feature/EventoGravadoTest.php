<?php

namespace Tests\Feature;

use App\Models\EventoGravado;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventoGravadoTest extends TestCase
{
    use RefreshDatabase;

    public function test_pode_criar_evento_gravado(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('eventos-gravados.store'), [
            'titulo' => 'Palestra sobre mineração',
            'data' => '2026-05-10',
            'youtube_url' => 'https://www.youtube.com/watch?v=abc123',
        ])->assertRedirect(route('eventos.index'));

        $this->assertDatabaseHas('evento_gravados', [
            'titulo' => 'Palestra sobre mineração',
            'youtube_url' => 'https://www.youtube.com/watch?v=abc123',
        ]);
    }

    public function test_pode_editar_evento_gravado(): void
    {
        $user = User::factory()->create();
        $gravado = EventoGravado::create([
            'titulo' => 'Original',
            'data' => '2026-01-01',
            'youtube_url' => 'https://youtu.be/original',
        ]);

        $this->actingAs($user)->get(route('eventos-gravados.edit', $gravado))
            ->assertOk()
            ->assertSee('Original');

        $this->actingAs($user)->put(route('eventos-gravados.update', $gravado), [
            'titulo' => 'Editado',
            'data' => '2026-01-02',
            'youtube_url' => 'https://youtu.be/editado',
        ])->assertRedirect(route('eventos.index'));

        $this->assertDatabaseHas('evento_gravados', ['titulo' => 'Editado']);
    }

    public function test_pode_remover_evento_gravado(): void
    {
        $user = User::factory()->create();
        $gravado = EventoGravado::create([
            'titulo' => 'Para remover',
            'data' => '2026-01-01',
            'youtube_url' => 'https://youtu.be/remover',
        ]);

        $this->actingAs($user)->delete(route('eventos-gravados.destroy', $gravado))
            ->assertRedirect(route('eventos.index'));

        $this->assertDatabaseMissing('evento_gravados', ['id' => $gravado->id]);
    }

    public function test_pagina_de_eventos_lista_eventos_gravados_com_link_para_youtube(): void
    {
        $user = User::factory()->create();
        EventoGravado::create([
            'titulo' => 'Webinar CETEM',
            'data' => '2026-02-15',
            'youtube_url' => 'https://www.youtube.com/watch?v=webinar',
        ]);

        $this->actingAs($user)->get(route('eventos.index'))
            ->assertOk()
            ->assertSee('Eventos gravados')
            ->assertSee('Webinar CETEM')
            ->assertSee('https://www.youtube.com/watch?v=webinar', false);
    }

    public function test_cadastro_em_lote_de_eventos_gravados(): void
    {
        $user = User::factory()->create();

        $csv = "titulo,data,youtube_url\n";
        $csv .= "Evento em lote,15/03/2026,https://www.youtube.com/watch?v=lote\n";
        $csv .= "Evento invalido,data-invalida,https://www.youtube.com/watch?v=erro\n";
        $csv .= "Evento url invalida,15/03/2026,https://vimeo.com/12345\n";

        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('eventos.csv', $csv);

        $response = $this->actingAs($user)->post(route('eventos-gravados.lote.import'), [
            'csv' => $file,
        ]);

        $response->assertRedirect(route('eventos-gravados.lote.form'));
        $this->assertDatabaseHas('evento_gravados', ['titulo' => 'Evento em lote']);
        $this->assertDatabaseMissing('evento_gravados', ['titulo' => 'Evento invalido']);
        $this->assertDatabaseMissing('evento_gravados', ['titulo' => 'Evento url invalida']);
        $this->assertEquals(1, EventoGravado::count());
    }
}
