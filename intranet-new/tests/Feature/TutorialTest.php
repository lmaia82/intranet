<?php

namespace Tests\Feature;

use App\Models\Tutorial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TutorialTest extends TestCase
{
    use RefreshDatabase;

    public function test_pode_criar_tutorial(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('tutoriais.store'), [
            'titulo' => 'Como usar o Repositório',
            'data' => '2026-05-10',
            'youtube_url' => 'https://www.youtube.com/watch?v=abc123',
        ])->assertRedirect(route('tutoriais.index'));

        $this->assertDatabaseHas('tutoriais', [
            'titulo' => 'Como usar o Repositório',
            'youtube_url' => 'https://www.youtube.com/watch?v=abc123',
        ]);
    }

    public function test_pode_editar_tutorial(): void
    {
        $user = User::factory()->create();
        $tutorial = Tutorial::create([
            'titulo' => 'Original',
            'data' => '2026-01-01',
            'youtube_url' => 'https://youtu.be/original',
        ]);

        $this->actingAs($user)->get(route('tutoriais.edit', $tutorial))
            ->assertOk()
            ->assertSee('Original');

        $this->actingAs($user)->put(route('tutoriais.update', $tutorial), [
            'titulo' => 'Editado',
            'data' => '2026-01-02',
            'youtube_url' => 'https://youtu.be/editado',
        ])->assertRedirect(route('tutoriais.index'));

        $this->assertDatabaseHas('tutoriais', ['titulo' => 'Editado']);
    }

    public function test_pode_remover_tutorial(): void
    {
        $user = User::factory()->create();
        $tutorial = Tutorial::create([
            'titulo' => 'Para remover',
            'data' => '2026-01-01',
            'youtube_url' => 'https://youtu.be/remover',
        ]);

        $this->actingAs($user)->delete(route('tutoriais.destroy', $tutorial))
            ->assertRedirect(route('tutoriais.index'));

        $this->assertDatabaseMissing('tutoriais', ['id' => $tutorial->id]);
    }

    public function test_pagina_de_tutoriais_lista_tutoriais_com_link_para_youtube(): void
    {
        $user = User::factory()->create();
        Tutorial::create([
            'titulo' => 'Webinar CETEM',
            'data' => '2026-02-15',
            'youtube_url' => 'https://www.youtube.com/watch?v=webinar',
        ]);

        $this->actingAs($user)->get(route('tutoriais.index'))
            ->assertOk()
            ->assertSee('Webinar CETEM')
            ->assertSee('https://www.youtube.com/watch?v=webinar', false);
    }

    public function test_cadastro_em_lote_de_tutoriais(): void
    {
        $user = User::factory()->create();

        $csv = "titulo,data,youtube_url\n";
        $csv .= "Tutorial em lote,15/03/2026,https://www.youtube.com/watch?v=lote\n";
        $csv .= "Tutorial invalido,data-invalida,https://www.youtube.com/watch?v=erro\n";
        $csv .= "Tutorial url invalida,15/03/2026,https://vimeo.com/12345\n";

        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('tutoriais.csv', $csv);

        $response = $this->actingAs($user)->post(route('tutoriais.lote.import'), [
            'csv' => $file,
        ]);

        $response->assertRedirect(route('tutoriais.lote.form'));
        $this->assertDatabaseHas('tutoriais', ['titulo' => 'Tutorial em lote']);
        $this->assertDatabaseMissing('tutoriais', ['titulo' => 'Tutorial invalido']);
        $this->assertDatabaseMissing('tutoriais', ['titulo' => 'Tutorial url invalida']);
        $this->assertEquals(1, Tutorial::count());
    }

    public function test_usuario_sem_permissao_nao_acessa_tutoriais(): void
    {
        $user = User::factory()->create(['group_id' => null]);

        $this->actingAs($user)->get(route('tutoriais.index'))->assertForbidden();
    }
}
