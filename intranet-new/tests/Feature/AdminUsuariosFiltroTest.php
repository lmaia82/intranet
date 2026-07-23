<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUsuariosFiltroTest extends TestCase
{
    use RefreshDatabase;

    public function test_mostra_total_geral_e_total_apos_filtro(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'name' => 'Fulano de Tal']);
        User::factory()->create(['name' => 'Beltrano da Silva']);
        User::factory()->create(['name' => 'Ciclano Pereira']);

        // 3 usuários no total (admin + 2), filtrando por nome sobra só 1.
        $response = $this->actingAs($admin)->get(route('admin.usuarios', ['nome' => 'Fulano']));

        $response->assertOk()
            ->assertSee('Exibindo')
            ->assertSeeInOrder(['1', 'de', '3']);
    }

    public function test_filtra_por_nome(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        User::factory()->create(['name' => 'Fulano de Tal']);
        User::factory()->create(['name' => 'Beltrano da Silva']);

        $response = $this->actingAs($admin)->get(route('admin.usuarios', ['nome' => 'fulano']));

        $response->assertOk()->assertSee('Fulano de Tal')->assertDontSee('Beltrano da Silva');
    }

    public function test_filtra_por_email(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        User::factory()->create(['email' => 'alvo@cetem.gov.br']);
        User::factory()->create(['email' => 'outro@cetem.gov.br']);

        $response = $this->actingAs($admin)->get(route('admin.usuarios', ['email' => 'alvo@']));

        $response->assertOk()->assertSee('alvo@cetem.gov.br')->assertDontSee('outro@cetem.gov.br');
    }

    public function test_filtra_por_setor_intranet(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $ti = Sector::create(['sigla' => 'TI']);
        $rh = Sector::create(['sigla' => 'RH']);

        $daTi = User::factory()->create(['name' => 'Usuario TI', 'sector_id' => $ti->id]);
        $daRh = User::factory()->create(['name' => 'Usuario RH', 'sector_id' => $rh->id]);

        $response = $this->actingAs($admin)->get(route('admin.usuarios', ['sector_id' => $ti->id]));

        $response->assertOk()->assertSee('Usuario TI')->assertDontSee('Usuario RH');
    }

    public function test_filtra_por_setor_do_ad(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        User::factory()->create(['name' => 'Usuario Sein', 'ad_setor' => 'SEIN']);
        User::factory()->create(['name' => 'Usuario Adm', 'ad_setor' => 'ADM']);

        $response = $this->actingAs($admin)->get(route('admin.usuarios', ['ad_setor' => 'SEIN']));

        $response->assertOk()->assertSee('Usuario Sein')->assertDontSee('Usuario Adm');
    }

    public function test_filtra_por_confere(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $setor = Sector::create(['sigla' => 'TI']);

        User::factory()->create(['name' => 'Convergente', 'ad_guid' => 'g1', 'ad_setor' => 'TI', 'sector_id' => $setor->id]);
        User::factory()->create(['name' => 'Divergente', 'ad_guid' => 'g2', 'ad_setor' => 'RH', 'sector_id' => $setor->id]);
        User::factory()->create(['name' => 'SemContaAd', 'ad_guid' => null]);

        $this->actingAs($admin)->get(route('admin.usuarios', ['confere' => 'sim']))
            ->assertOk()->assertSee('Convergente')->assertDontSee('Divergente')->assertDontSee('SemContaAd');

        $this->actingAs($admin)->get(route('admin.usuarios', ['confere' => 'nao']))
            ->assertOk()->assertSee('Divergente')->assertDontSee('Convergente')->assertDontSee('SemContaAd');

        $this->actingAs($admin)->get(route('admin.usuarios', ['confere' => 'sem_ad']))
            ->assertOk()->assertSee('SemContaAd')->assertDontSee('Convergente')->assertDontSee('Divergente');
    }

    public function test_filtra_por_grupo(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $grupoA = Group::create(['name' => 'Grupo A']);
        $grupoB = Group::create(['name' => 'Grupo B']);

        User::factory()->create(['name' => 'Do Grupo A', 'group_id' => $grupoA->id]);
        User::factory()->create(['name' => 'Do Grupo B', 'group_id' => $grupoB->id]);

        $response = $this->actingAs($admin)->get(route('admin.usuarios', ['group_id' => $grupoA->id]));

        $response->assertOk()->assertSee('Do Grupo A')->assertDontSee('Do Grupo B');
    }

    public function test_filtra_por_admin(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'name' => 'Sou Admin']);
        User::factory()->create(['is_admin' => false, 'name' => 'Nao Sou Admin']);

        $response = $this->actingAs($admin)->get(route('admin.usuarios', ['is_admin' => '1']));

        $response->assertOk()->assertSee('Sou Admin')->assertDontSee('Nao Sou Admin');
    }

    public function test_lista_mostra_data_de_criacao(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $usuario = User::factory()->create(['created_at' => '2026-01-15']);

        $response = $this->actingAs($admin)->get(route('admin.usuarios'));

        $response->assertOk()->assertSee($usuario->created_at->format('d/m/Y'));
    }
}
