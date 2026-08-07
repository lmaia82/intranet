<?php

namespace Tests\Feature;

use App\Models\ReservaSala;
use App\Models\Sala;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeReservaSalaViewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_telas_de_reserva_de_sala_renderizam(): void
    {
        $user = User::factory()->create();
        $sala = Sala::where('nome', 'Sala 10')->first();

        $reserva = ReservaSala::create([
            'sala_id' => $sala->id,
            'user_id' => $user->id,
            'titulo' => 'Reunião de exemplo',
            'data' => now()->addDays(3)->toDateString(),
            'horario_inicio' => '09:00',
            'horario_fim' => '10:00',
            'tipo_evento' => 'Reunião',
            'publico' => 'presencial',
            'equipamentos' => ['Nenhum'],
            'servicos' => ['Nenhum'],
            'comunicacao' => ['Nenhum'],
        ]);

        $this->actingAs($user)->get(route('reservas-sala.index'))->assertOk()->assertSee('Reserva de Sala');
        $this->actingAs($user)->get(route('reservas-sala.index', ['q' => 'exemplo']))->assertOk()->assertSee('Reunião de exemplo');
        $this->actingAs($user)->get(route('reservas-sala.create'))->assertOk();
        $this->actingAs($user)->get(route('reservas-sala.show', $reserva))->assertOk()->assertSee('Reunião de exemplo');
        $this->actingAs($user)->get(route('reservas-sala.edit', $reserva))->assertOk();
        $this->actingAs($user)->get(route('reservas-sala.relatorios'))->assertOk();
        $this->actingAs($user)->get(route('reservas-sala.imprimir-semana'))->assertOk();
    }

    public function test_dashboard_renderiza_com_reserva_de_sala_no_calendario(): void
    {
        $user = User::factory()->create();
        $sala = Sala::where('nome', 'Sala 10')->first();

        ReservaSala::create([
            'sala_id' => $sala->id,
            'user_id' => $user->id,
            'titulo' => 'Reunião de exemplo',
            'data' => now()->toDateString(),
            'horario_inicio' => '09:00',
            'horario_fim' => '10:00',
            'tipo_evento' => 'Reunião',
            'publico' => 'presencial',
            'equipamentos' => ['Nenhum'],
            'servicos' => ['Nenhum'],
            'comunicacao' => ['Nenhum'],
        ]);

        $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertSee('Reunião de exemplo');
    }
}
