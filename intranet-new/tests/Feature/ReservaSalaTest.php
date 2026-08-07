<?php

namespace Tests\Feature;

use App\Models\ReservaSala;
use App\Models\Sala;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservaSalaTest extends TestCase
{
    use RefreshDatabase;

    private function dadosBasicos(array $sobrescreve = []): array
    {
        return array_merge([
            'titulo' => 'Reunião de alinhamento',
            'data' => now()->addDays(3)->toDateString(),
            'data_termino' => now()->addDays(3)->toDateString(),
            'horario_inicio' => '09:00',
            'horario_fim' => '10:00',
            'tipo_evento' => 'Reunião',
            'publico' => 'presencial',
            'visita_externa' => '0',
            'equipamentos' => ['Nenhum'],
            'servicos' => ['Nenhum'],
            'comunicacao' => ['Nenhum'],
        ], $sobrescreve);
    }

    public function test_salas_iniciais_foram_importadas_do_sistema_do_sein(): void
    {
        $this->assertSame(7, Sala::count());
        $this->assertTrue(Sala::where('nome', 'Pontes de Miranda')->value('restrita'));
        $this->assertTrue(Sala::where('nome', 'Sala COPTM')->value('restrita'));
        $this->assertTrue(Sala::where('nome', 'Dias Leite (VIP)')->value('permite_arrumacao'));
    }

    public function test_usuario_sem_permissao_nao_acessa_reserva_de_sala(): void
    {
        $user = User::factory()->create(['group_id' => null]);

        $this->actingAs($user)->get(route('reservas-sala.index'))->assertForbidden();
        $this->actingAs($user)->get(route('reservas-sala.create'))->assertForbidden();
    }

    public function test_qualquer_colaborador_pode_criar_uma_reserva(): void
    {
        $user = User::factory()->create();
        $sala = Sala::where('nome', 'Sala 10')->first();

        $response = $this->actingAs($user)->post(route('reservas-sala.store'), $this->dadosBasicos(['sala_id' => $sala->id]));

        $response->assertRedirect(route('reservas-sala.index'));
        $this->assertDatabaseHas('reserva_salas', [
            'titulo' => 'Reunião de alinhamento',
            'sala_id' => $sala->id,
            'user_id' => $user->id,
        ]);
        $reserva = ReservaSala::first();
        $this->assertSame($user->name, $reserva->solicitante);
    }

    public function test_conflito_de_horario_na_mesma_sala_e_bloqueado(): void
    {
        $user = User::factory()->create();
        $sala = Sala::where('nome', 'Sala 10')->first();

        ReservaSala::create([
            'sala_id' => $sala->id,
            'user_id' => $user->id,
            'titulo' => 'Já marcada',
            'data' => now()->addDays(3)->toDateString(),
            'horario_inicio' => '09:00',
            'horario_fim' => '10:00',
            'tipo_evento' => 'Reunião',
            'publico' => 'presencial',
            'equipamentos' => ['Nenhum'],
            'servicos' => ['Nenhum'],
            'comunicacao' => ['Nenhum'],
        ]);

        // Início às 09:30 — só 30 minutos depois do início da reserva já
        // existente (09:00), dentro da margem de 1h exigida entre reservas.
        $response = $this->actingAs($user)->post(route('reservas-sala.store'), $this->dadosBasicos([
            'sala_id' => $sala->id,
            'horario_inicio' => '09:30',
            'horario_fim' => '10:30',
        ]));

        $response->assertSessionHasErrors('horario_inicio');
        $this->assertSame(1, ReservaSala::count());
    }

    public function test_horario_com_intervalo_de_uma_hora_nao_e_bloqueado(): void
    {
        $user = User::factory()->create();
        $sala = Sala::where('nome', 'Sala 10')->first();

        ReservaSala::create([
            'sala_id' => $sala->id,
            'user_id' => $user->id,
            'titulo' => 'Já marcada',
            'data' => now()->addDays(3)->toDateString(),
            'horario_inicio' => '09:00',
            'horario_fim' => '10:00',
            'tipo_evento' => 'Reunião',
            'publico' => 'presencial',
            'equipamentos' => ['Nenhum'],
            'servicos' => ['Nenhum'],
            'comunicacao' => ['Nenhum'],
        ]);

        $response = $this->actingAs($user)->post(route('reservas-sala.store'), $this->dadosBasicos([
            'sala_id' => $sala->id,
            'horario_inicio' => '11:00',
            'horario_fim' => '12:00',
        ]));

        $response->assertRedirect(route('reservas-sala.index'));
        $this->assertSame(2, ReservaSala::count());
    }

    public function test_sala_restrita_exige_confirmacao_de_autorizacao(): void
    {
        $user = User::factory()->create();
        $sala = Sala::where('nome', 'Pontes de Miranda')->first();

        $response = $this->actingAs($user)->post(route('reservas-sala.store'), $this->dadosBasicos(['sala_id' => $sala->id]));

        $response->assertSessionHasErrors('permissao_especial');
        $this->assertSame(0, ReservaSala::count());

        $response = $this->actingAs($user)->post(route('reservas-sala.store'), $this->dadosBasicos([
            'sala_id' => $sala->id,
            'permissao_especial' => '1',
        ]));

        $response->assertRedirect(route('reservas-sala.index'));
        $this->assertSame(1, ReservaSala::count());
    }

    public function test_arrumacao_so_e_aceita_na_sala_vip(): void
    {
        $user = User::factory()->create();
        $salaVip = Sala::where('nome', 'Dias Leite (VIP)')->first();
        $salaComum = Sala::where('nome', 'Sala 10')->first();

        // Sala VIP exige arrumação.
        $response = $this->actingAs($user)->post(route('reservas-sala.store'), $this->dadosBasicos(['sala_id' => $salaVip->id]));
        $response->assertSessionHasErrors('arrumacao');

        $response = $this->actingAs($user)->post(route('reservas-sala.store'), $this->dadosBasicos([
            'sala_id' => $salaVip->id,
            'arrumacao' => 'interna',
        ]));
        $response->assertRedirect(route('reservas-sala.index'));
        $this->assertSame('interna', ReservaSala::first()->arrumacao);

        // Sala comum: mesmo se mandar arrumação, é ignorada/zerada.
        $response = $this->actingAs($user)->post(route('reservas-sala.store'), $this->dadosBasicos([
            'sala_id' => $salaComum->id,
            'arrumacao' => 'interna',
            'data' => now()->addDays(4)->toDateString(),
            'data_termino' => now()->addDays(4)->toDateString(),
        ]));
        $response->assertRedirect(route('reservas-sala.index'));
        $this->assertNull(ReservaSala::latest('id')->first()->arrumacao);
    }

    public function test_sala_virtual_exige_plataforma_e_divulgacao_exige_onde(): void
    {
        $user = User::factory()->create();
        $sala = Sala::where('nome', 'Sala 10')->first();

        $response = $this->actingAs($user)->post(route('reservas-sala.store'), $this->dadosBasicos([
            'sala_id' => $sala->id,
            'equipamentos' => ['Virtual'],
            'comunicacao' => ['Divulgação'],
        ]));
        $response->assertSessionHasErrors(['salavirtual', 'divulgacao']);
        $this->assertSame(0, ReservaSala::count());

        $response = $this->actingAs($user)->post(route('reservas-sala.store'), $this->dadosBasicos([
            'sala_id' => $sala->id,
            'equipamentos' => ['Virtual'],
            'salavirtual' => 'rnppessoal',
            'comunicacao' => ['Divulgação'],
            'divulgacao' => 'interna',
        ]));
        $response->assertRedirect(route('reservas-sala.index'));
        $this->assertSame(1, ReservaSala::count());
    }

    public function test_wifi_visitante_evento_exige_nome_do_evento(): void
    {
        $user = User::factory()->create();
        $sala = Sala::where('nome', 'Sala 10')->first();

        $response = $this->actingAs($user)->post(route('reservas-sala.store'), $this->dadosBasicos([
            'sala_id' => $sala->id,
            'equipamentos' => ['WiFi Visitante'],
            'wifi_tipo' => 'evento',
        ]));
        $response->assertSessionHasErrors('wifi_nome_evento');

        $response = $this->actingAs($user)->post(route('reservas-sala.store'), $this->dadosBasicos([
            'sala_id' => $sala->id,
            'equipamentos' => ['WiFi Visitante'],
            'wifi_tipo' => 'evento',
            'wifi_nome_evento' => 'Semana da Mineração',
        ]));
        $response->assertRedirect(route('reservas-sala.index'));
        $this->assertSame('Semana da Mineração', ReservaSala::first()->wifi_nome_evento);
    }

    public function test_apenas_o_dono_ou_admin_pode_editar_ou_excluir(): void
    {
        $dono = User::factory()->create();
        $outroUsuario = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $sala = Sala::where('nome', 'Sala 10')->first();

        $reserva = ReservaSala::create([
            'sala_id' => $sala->id,
            'user_id' => $dono->id,
            'titulo' => 'Reunião do dono',
            'data' => now()->addDays(3)->toDateString(),
            'horario_inicio' => '09:00',
            'horario_fim' => '10:00',
            'tipo_evento' => 'Reunião',
            'publico' => 'presencial',
            'equipamentos' => ['Nenhum'],
            'servicos' => ['Nenhum'],
            'comunicacao' => ['Nenhum'],
        ]);

        $this->actingAs($outroUsuario)->get(route('reservas-sala.edit', $reserva))->assertForbidden();
        $this->actingAs($outroUsuario)->delete(route('reservas-sala.destroy', $reserva))->assertForbidden();

        $this->actingAs($admin)->get(route('reservas-sala.edit', $reserva))->assertOk();

        $this->actingAs($dono)->delete(route('reservas-sala.destroy', $reserva));
        $this->assertDatabaseMissing('reserva_salas', ['id' => $reserva->id]);
    }

    public function test_dashboard_mostra_reserva_apenas_para_quem_reservou(): void
    {
        $dono = User::factory()->create();
        $outroUsuario = User::factory()->create();
        $sala = Sala::where('nome', 'Sala 10')->first();

        ReservaSala::create([
            'sala_id' => $sala->id,
            'user_id' => $dono->id,
            'titulo' => 'Reunião só do dono',
            'data' => now()->toDateString(),
            'horario_inicio' => '09:00',
            'horario_fim' => '10:00',
            'tipo_evento' => 'Reunião',
            'publico' => 'presencial',
            'equipamentos' => ['Nenhum'],
            'servicos' => ['Nenhum'],
            'comunicacao' => ['Nenhum'],
        ]);

        $this->actingAs($dono)->get(route('dashboard'))->assertOk()->assertSee('Reunião só do dono');
        $this->actingAs($outroUsuario)->get(route('dashboard'))->assertOk()->assertDontSee('Reunião só do dono');
    }
}
