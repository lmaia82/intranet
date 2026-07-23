<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminEditarUsuarioTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_ve_formulario_de_edicao_com_dados_atuais(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $usuario = User::factory()->create(['name' => 'Fulano de Tal', 'email' => 'fulano@cetem.gov.br']);

        $this->actingAs($admin)->get(route('admin.usuarios.editar', $usuario))
            ->assertOk()
            ->assertSee('Fulano de Tal')
            ->assertSee('fulano@cetem.gov.br');
    }

    public function test_admin_atualiza_nome_e_email_do_usuario(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $usuario = User::factory()->create(['name' => 'Nome Antigo', 'email' => 'antigo@cetem.gov.br']);

        $response = $this->actingAs($admin)->put(route('admin.usuarios.update', $usuario), [
            'name' => 'Nome Novo',
            'email' => 'novo@cetem.gov.br',
        ]);

        $response->assertRedirect(route('admin.usuarios'));
        $usuario->refresh();
        $this->assertEquals('Nome Novo', $usuario->name);
        $this->assertEquals('novo@cetem.gov.br', $usuario->email);
    }

    public function test_admin_troca_a_senha_do_usuario(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $usuario = User::factory()->create(['password' => Hash::make('senha-antiga')]);
        $senhaAntigaHash = $usuario->password;

        $this->actingAs($admin)->put(route('admin.usuarios.update', $usuario), [
            'name' => $usuario->name,
            'email' => $usuario->email,
            'password' => 'senha-nova-123',
            'password_confirmation' => 'senha-nova-123',
        ]);

        $usuario->refresh();
        $this->assertNotEquals($senhaAntigaHash, $usuario->password);
        $this->assertTrue(Hash::check('senha-nova-123', $usuario->password));
    }

    public function test_senha_em_branco_mantem_a_senha_atual(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $usuario = User::factory()->create(['password' => Hash::make('senha-atual')]);
        $senhaHash = $usuario->password;

        $this->actingAs($admin)->put(route('admin.usuarios.update', $usuario), [
            'name' => $usuario->name,
            'email' => $usuario->email,
        ]);

        $this->assertEquals($senhaHash, $usuario->fresh()->password);
    }

    public function test_email_duplicado_falha_validacao(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $usuario = User::factory()->create(['email' => 'usuario@cetem.gov.br']);
        User::factory()->create(['email' => 'outro@cetem.gov.br']);

        $response = $this->actingAs($admin)->put(route('admin.usuarios.update', $usuario), [
            'name' => $usuario->name,
            'email' => 'outro@cetem.gov.br',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_manter_o_proprio_email_ao_editar_nao_falha_validacao(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'email' => 'admin@cetem.gov.br']);

        $response = $this->actingAs($admin)->put(route('admin.usuarios.update', $admin), [
            'name' => 'Admin Renomeado',
            'email' => 'admin@cetem.gov.br',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals('Admin Renomeado', $admin->fresh()->name);
    }

    public function test_campo_de_senha_nao_aparece_para_usuario_vinculado_ao_ad(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $usuario = User::factory()->create(['ad_guid' => 'guid-123']);

        $this->actingAs($admin)->get(route('admin.usuarios.editar', $usuario))
            ->assertOk()
            ->assertDontSee('Nova senha')
            ->assertSee('validada no Active Directory');
    }

    public function test_campo_de_senha_aparece_para_usuario_local(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $usuario = User::factory()->create(['ad_guid' => null]);

        $this->actingAs($admin)->get(route('admin.usuarios.editar', $usuario))
            ->assertOk()
            ->assertSee('Nova senha');
    }

    public function test_admin_nao_consegue_trocar_senha_de_usuario_vinculado_ao_ad(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $usuario = User::factory()->create(['ad_guid' => 'guid-456', 'password' => Hash::make('senha-antiga')]);
        $senhaAntigaHash = $usuario->password;

        $this->actingAs($admin)->put(route('admin.usuarios.update', $usuario), [
            'name' => $usuario->name,
            'email' => $usuario->email,
            'password' => 'senha-nova-123',
            'password_confirmation' => 'senha-nova-123',
        ]);

        $this->assertEquals($senhaAntigaHash, $usuario->fresh()->password);
    }

    public function test_usuario_nao_admin_nao_acessa_edicao(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $outro = User::factory()->create();

        $this->actingAs($user)->get(route('admin.usuarios.editar', $outro))->assertForbidden();
        $this->actingAs($user)->put(route('admin.usuarios.update', $outro), [
            'name' => 'Tentativa',
            'email' => 'tentativa@cetem.gov.br',
        ])->assertForbidden();
    }
}
