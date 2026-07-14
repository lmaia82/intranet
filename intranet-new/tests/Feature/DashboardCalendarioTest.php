<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardCalendarioTest extends TestCase
{
    use RefreshDatabase;

    public function test_calendario_mostra_mes_atual_por_padrao(): void
    {
        $user = User::factory()->create();

        $mesesPtBr = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
        ];
        $esperado = $mesesPtBr[now()->month] . ' de ' . now()->year;

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee($esperado);
    }

    public function test_navegacao_para_mes_especifico_via_query_string(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('dashboard', ['mes' => 1, 'ano' => 2026]))
            ->assertOk()
            ->assertSee('Janeiro de 2026');
    }

    public function test_evento_aparece_no_dia_correto_do_calendario(): void
    {
        $user = User::factory()->create();

        Evento::create([
            'title' => 'Reunião de planejamento',
            'local' => 'Sala 3',
            'dt_start' => '2026-03-10',
        ]);

        $this->actingAs($user)->get(route('dashboard', ['mes' => 3, 'ano' => 2026]))
            ->assertOk()
            ->assertSee('Reunião de planejamento')
            ->assertSee('Sala 3');
    }

    public function test_mes_invalido_cai_para_mes_atual(): void
    {
        $user = User::factory()->create();

        $mesesPtBr = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
        ];
        $esperado = $mesesPtBr[now()->month] . ' de ' . now()->year;

        $this->actingAs($user)->get(route('dashboard', ['mes' => 99, 'ano' => now()->year]))
            ->assertOk()
            ->assertSee($esperado);
    }
}
