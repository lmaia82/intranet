<?php

namespace Tests\Feature;

use App\Models\Arquivo;
use App\Models\EventoGravado;
use App\Models\Pasta;
use App\Models\Sector;
use App\Models\Tutorial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardWidgetsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_mostra_tutoriais_eventos_gravados_e_documentos_publicos_recentes(): void
    {
        $user = User::factory()->create();
        $sector = Sector::first() ?? Sector::create(['name' => 'TI']);

        Tutorial::create(['titulo' => 'Tutorial Recente', 'data' => '2026-01-01', 'youtube_url' => 'https://youtu.be/tut']);
        EventoGravado::create(['titulo' => 'Gravado Recente', 'data' => '2026-01-01', 'youtube_url' => 'https://youtu.be/grav']);

        $pasta = Pasta::create(['nome' => 'Pública', 'sector_id' => $sector->id, 'is_private' => false]);
        Arquivo::create([
            'pasta_id' => $pasta->id,
            'nome_original' => 'documento-publico.pdf',
            'caminho' => 'uploads/documento-publico.pdf',
            'extensao' => 'pdf',
            'tamanho' => 100,
            'sector_id' => $sector->id,
            'is_private' => false,
        ]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Tutorial Recente')
            ->assertSee('Gravado Recente')
            ->assertSee('documento-publico.pdf');
    }

    public function test_dashboard_esconde_widgets_de_telas_sem_permissao(): void
    {
        $user = User::factory()->create(['group_id' => null]);

        Tutorial::create(['titulo' => 'Tutorial Oculto', 'data' => '2026-01-01', 'youtube_url' => 'https://youtu.be/tut']);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Tutorial Oculto')
            ->assertDontSee('Últimos Tutoriais');
    }
}
