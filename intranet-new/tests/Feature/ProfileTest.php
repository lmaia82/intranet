<?php

namespace Tests\Feature;

use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_page_mostra_somente_nome_email_e_lotacao(): void
    {
        $sector = Sector::create(['sigla' => 'COADM', 'nome' => 'Coordenação de Administração']);
        $user = User::factory()->create(['sector_id' => $sector->id]);

        $this->actingAs($user)->get('/profile')
            ->assertOk()
            ->assertSee($user->name)
            ->assertSee($user->email)
            ->assertSee('Coordenação de Administração');
    }

    public function test_usuario_nao_pode_atualizar_o_proprio_perfil(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch('/profile', ['name' => 'Outro Nome'])
            ->assertMethodNotAllowed();

        $this->assertSame($user->name, $user->fresh()->name);
    }

    public function test_usuario_nao_pode_excluir_a_propria_conta(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->delete('/profile', ['password' => 'password'])
            ->assertMethodNotAllowed();

        $this->assertNotNull($user->fresh());
    }

    public function test_dashboard_exibe_nome_e_sigla_da_lotacao_do_usuario(): void
    {
        $sector = Sector::create(['sigla' => 'COADM', 'nome' => 'Coordenação de Administração']);
        $user = User::factory()->create(['sector_id' => $sector->id]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Coordenação de Administração')
            ->assertSee('(COADM)', false)
            ->assertSee('Ver perfil');
    }
}
