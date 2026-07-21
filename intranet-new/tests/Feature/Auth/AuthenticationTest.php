<?php

namespace Tests\Feature\Auth;

use App\Models\Destaque;
use App\Models\Informativo;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200)
            ->assertSee("dispatch('open-modal', 'login')", false)
            ->assertSee('style="display: none;"', false)
            ->assertSee('Entre para acessar as funcionalidades')
            ->assertSee('Mural de Avisos');
    }

    public function test_login_screen_mostra_previa_de_informativos_publicos_e_esconde_privados(): void
    {
        $sector = Sector::create(['sigla' => 'TI']);
        Informativo::create(['title' => 'Aviso Público', 'content' => 'x', 'published_at' => now(), 'is_private' => false]);
        Informativo::create(['title' => 'Aviso Restrito', 'content' => 'x', 'sector_id' => $sector->id, 'published_at' => now(), 'is_private' => true]);

        $response = $this->get('/login');

        $response->assertOk()
            ->assertSee('Aviso Público')
            ->assertDontSee('Aviso Restrito');
    }

    public function test_login_screen_mostra_destaques_ativos(): void
    {
        Destaque::create(['titulo' => 'Campanha', 'imagem' => 'destaques/campanha.jpg', 'ordem' => 0, 'ativo' => true]);

        $response = $this->get('/login');

        $response->assertOk()->assertSee('destaques/campanha.jpg', false);
    }

    public function test_visualizar_imagem_publica_de_destaque_nao_atrapalha_redirecionamento_pos_login(): void
    {
        $sector = Sector::create(['sigla' => 'TI']);
        $user = User::factory()->create(['sector_id' => $sector->id]);

        $this->actingAs($user)->post(route('destaques.store'), [
            'titulo' => 'Campanha',
            'imagem' => \Illuminate\Http\UploadedFile::fake()->image('banner.png', 1600, 500),
            'inicio_em' => now()->format('Y-m-d\TH:i'),
            'fim_em' => now()->addDays(30)->format('Y-m-d\TH:i'),
        ]);
        $this->post('/logout');

        $destaque = Destaque::first();

        // Simula o navegador carregando a imagem do destaque na tela de
        // login, deslogado — isso não pode gravar essa URL como "intended".
        $this->get($destaque->imagemUrl())->assertOk();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_login_com_credenciais_invalidas_abre_o_popup_com_o_erro(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response = $this->get('/login');

        $response->assertOk()
            ->assertSee('style="display: block;"', false)
            ->assertSee('These credentials do not match our records.');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }
}
