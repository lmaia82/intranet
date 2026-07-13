<?php

namespace Tests\Feature;

use App\Models\Informativo;
use App\Models\InformativoEnvio;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InformativoNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_publicar_sem_marcar_notificacao_nao_envia_email(): void
    {
        Mail::fake();

        $admin = User::factory()->create();
        User::factory()->create();

        $this->actingAs($admin)->post(route('informativos.store'), [
            'title' => 'Aviso geral',
            'content' => 'Conteudo',
            'sector_id' => '',
            'is_private' => '0',
        ]);

        $informativo = Informativo::firstOrFail();
        $this->assertEquals(0, InformativoEnvio::where('informativo_id', $informativo->id)->count());
        Mail::assertNothingSent();
    }

    public function test_publicar_com_notificacao_marcada_notifica_todos_os_usuarios(): void
    {
        Mail::fake();

        $admin = User::factory()->create();
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('informativos.store'), [
            'title' => 'Aviso geral',
            'content' => 'Conteudo',
            'sector_id' => '',
            'is_private' => '0',
            'notificar_email' => '1',
        ]);

        $response->assertRedirect(route('informativos.index'));

        $informativo = Informativo::firstOrFail();
        $this->assertEquals(3, InformativoEnvio::where('informativo_id', $informativo->id)->count());
        Mail::assertSent(\App\Mail\NovoInformativoMail::class, 3);
    }

    public function test_publicar_com_notificacao_e_setor_notifica_apenas_o_setor(): void
    {
        Mail::fake();

        $admin = User::factory()->create();
        $sector = Sector::create(['name' => 'TI']);
        $doSetor = User::factory()->create(['sector_id' => $sector->id]);
        $foraDoSetor = User::factory()->create(['sector_id' => null]);

        $this->actingAs($admin)->post(route('informativos.store'), [
            'title' => 'Aviso do setor',
            'content' => 'Conteudo',
            'sector_id' => $sector->id,
            'is_private' => '1',
            'notificar_email' => '1',
        ]);

        $informativo = Informativo::firstOrFail();
        $envios = InformativoEnvio::where('informativo_id', $informativo->id)->pluck('email')->all();

        $this->assertEquals([$doSetor->email], $envios);
        $this->assertNotContains($foraDoSetor->email, $envios);
    }

    public function test_reenviar_dispara_lote_de_emails_mesmo_sem_notificacao_inicial(): void
    {
        Mail::fake();

        $admin = User::factory()->create();
        User::factory()->create();

        $this->actingAs($admin)->post(route('informativos.store'), [
            'title' => 'Aviso',
            'content' => 'Conteudo',
            'sector_id' => '',
            'is_private' => '0',
        ]);

        $informativo = Informativo::firstOrFail();
        $this->assertEquals(0, InformativoEnvio::count());

        $this->actingAs($admin)->post(route('informativos.reenviar', $informativo))
            ->assertRedirect(route('informativos.show', $informativo));

        $this->assertEquals(2, InformativoEnvio::count());
        Mail::assertSent(\App\Mail\NovoInformativoMail::class, 2);
    }

    public function test_pagina_show_exibe_historico_de_envios(): void
    {
        Mail::fake();

        $admin = User::factory()->create();

        $this->actingAs($admin)->post(route('informativos.store'), [
            'title' => 'Aviso',
            'content' => 'Conteudo',
            'sector_id' => '',
            'is_private' => '0',
            'notificar_email' => '1',
        ]);

        $informativo = Informativo::firstOrFail();

        $this->actingAs($admin)->get(route('informativos.show', $informativo))
            ->assertOk()
            ->assertSee('Envios por e-mail')
            ->assertSee($admin->email);
    }
}
