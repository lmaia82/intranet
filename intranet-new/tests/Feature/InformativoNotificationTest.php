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
        $sector = Sector::create(['sigla' => 'TI']);
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

    public function test_publicar_para_coordenacao_notifica_tambem_os_servicos_subordinados(): void
    {
        Mail::fake();

        $admin = User::factory()->create();
        $coordenacao = Sector::create(['sigla' => 'COADM']);
        $servico = Sector::create(['sigla' => 'SECOF', 'parent_id' => $coordenacao->id]);
        $outraCoordenacao = Sector::create(['sigla' => 'COAMI']);

        $daCoordenacao = User::factory()->create(['sector_id' => $coordenacao->id]);
        $doServico = User::factory()->create(['sector_id' => $servico->id]);
        $deFora = User::factory()->create(['sector_id' => $outraCoordenacao->id]);

        $this->actingAs($admin)->post(route('informativos.store'), [
            'title' => 'Aviso da coordenação',
            'content' => 'Conteudo',
            'sector_id' => $coordenacao->id,
            'is_private' => '1',
            'notificar_email' => '1',
        ]);

        $informativo = Informativo::firstOrFail();
        $envios = InformativoEnvio::where('informativo_id', $informativo->id)->pluck('email')->all();

        $this->assertContains($daCoordenacao->email, $envios);
        $this->assertContains($doServico->email, $envios);
        $this->assertNotContains($deFora->email, $envios);
    }

    public function test_reenviar_formulario_sugere_emails_do_setor_do_informativo(): void
    {
        $admin = User::factory()->create();
        $sector = Sector::create(['sigla' => 'TI']);
        $doSetor = User::factory()->create(['sector_id' => $sector->id]);
        User::factory()->create(['sector_id' => null]);

        $informativo = Informativo::create([
            'title' => 'Aviso do setor',
            'content' => 'Conteudo',
            'sector_id' => $sector->id,
            'is_private' => true,
            'published_at' => now(),
        ]);

        $this->actingAs($admin)->get(route('informativos.reenviar.form', $informativo))
            ->assertOk()
            ->assertSee($doSetor->email);
    }

    public function test_reenviar_formulario_sugere_tambem_emails_dos_servicos_subordinados(): void
    {
        $admin = User::factory()->create();
        $coordenacao = Sector::create(['sigla' => 'COADM']);
        $servico = Sector::create(['sigla' => 'SECOF', 'parent_id' => $coordenacao->id]);
        $doServico = User::factory()->create(['sector_id' => $servico->id]);

        $informativo = Informativo::create([
            'title' => 'Aviso da coordenação',
            'content' => 'Conteudo',
            'sector_id' => $coordenacao->id,
            'is_private' => true,
            'published_at' => now(),
        ]);

        $this->actingAs($admin)->get(route('informativos.reenviar.form', $informativo))
            ->assertOk()
            ->assertSee($doServico->email);
    }

    public function test_reenviar_permite_editar_lista_de_destinatarios(): void
    {
        Mail::fake();

        $admin = User::factory()->create();
        $sector = Sector::create(['sigla' => 'TI']);
        $doSetor = User::factory()->create(['sector_id' => $sector->id]);

        $informativo = Informativo::create([
            'title' => 'Aviso',
            'content' => 'Conteudo',
            'sector_id' => $sector->id,
            'is_private' => true,
            'published_at' => now(),
        ]);

        $this->assertEquals(0, InformativoEnvio::count());

        $emailsEditados = $doSetor->email . "\nexterno@cetem.gov.br";

        $this->actingAs($admin)->post(route('informativos.reenviar', $informativo), [
            'emails' => $emailsEditados,
        ])->assertRedirect(route('informativos.show', $informativo));

        $enviados = InformativoEnvio::pluck('email')->sort()->values()->all();
        $this->assertEquals(collect(['externo@cetem.gov.br', $doSetor->email])->sort()->values()->all(), $enviados);
        Mail::assertSent(\App\Mail\NovoInformativoMail::class, 2);
    }

    public function test_reenviar_rejeita_email_invalido(): void
    {
        Mail::fake();

        $admin = User::factory()->create();
        $informativo = Informativo::create([
            'title' => 'Aviso',
            'content' => 'Conteudo',
            'published_at' => now(),
        ]);

        $this->actingAs($admin)->post(route('informativos.reenviar', $informativo), [
            'emails' => "nao-e-email\nvalido@cetem.gov.br",
        ])->assertSessionHasErrors('emails');

        $this->assertEquals(0, InformativoEnvio::count());
        Mail::assertNothingSent();
    }

    public function test_falha_no_envio_de_um_email_nao_aborta_o_restante_do_lote(): void
    {
        Mail::shouldReceive('to')->andReturnUsing(function ($email) {
            return new class($email) {
                public function __construct(private $email)
                {
                }

                public function send($mailable)
                {
                    if ($this->email === 'falha@cetem.gov.br') {
                        throw new \Exception('Falha simulada de SMTP');
                    }

                    return true;
                }
            };
        });

        $admin = User::factory()->create();
        $informativo = Informativo::create([
            'title' => 'Aviso',
            'content' => 'Conteudo',
            'published_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post(route('informativos.reenviar', $informativo), [
            'emails' => "ok@cetem.gov.br\nfalha@cetem.gov.br",
        ]);

        $response->assertRedirect(route('informativos.show', $informativo))
            ->assertSessionHas('status', 'E-mail reenviado para 1 destinatário(s). 1 falha(s) no envio.');

        $this->assertDatabaseHas('informativo_envios', ['email' => 'ok@cetem.gov.br', 'sucesso' => true]);
        $this->assertDatabaseHas('informativo_envios', ['email' => 'falha@cetem.gov.br', 'sucesso' => false]);

        $envioComFalha = InformativoEnvio::where('email', 'falha@cetem.gov.br')->first();
        $this->assertStringContainsString('Falha simulada de SMTP', $envioComFalha->erro);
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
