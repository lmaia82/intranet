<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminUsuarioLoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_cadastro_em_lote_de_usuarios(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $sector = Sector::create(['name' => 'TI']);
        $group = Group::firstOrCreate(['name' => 'Colaboradores']);
        $existente = User::factory()->create(['email' => 'ja-existe@cetem.gov.br']);

        $csv = "nome,email,senha,setor,grupo,admin\n";
        $csv .= "Fulano de Tal,fulano@cetem.gov.br,SenhaProvisoria123,TI,Colaboradores,nao\n";
        $csv .= "Setor Errado,setorerrado@cetem.gov.br,Senha123,Inexistente,Colaboradores,nao\n";
        $csv .= "Ja Existe,ja-existe@cetem.gov.br,Senha123,TI,Colaboradores,nao\n";
        $csv .= "Sem Senha,semsenha@cetem.gov.br,,TI,Colaboradores,nao\n";

        $file = UploadedFile::fake()->createWithContent('usuarios.csv', $csv);

        $response = $this->actingAs($admin)->post(route('admin.usuarios.lote.import'), ['csv' => $file]);

        $response->assertRedirect(route('admin.usuarios.lote.form'));

        $novo = User::where('email', 'fulano@cetem.gov.br')->first();
        $this->assertNotNull($novo);
        $this->assertEquals($sector->id, $novo->sector_id);
        $this->assertEquals($group->id, $novo->group_id);
        $this->assertFalse($novo->is_admin);

        $this->assertDatabaseMissing('users', ['email' => 'setorerrado@cetem.gov.br']);
        $this->assertDatabaseMissing('users', ['email' => 'semsenha@cetem.gov.br']);
        $this->assertEquals(1, User::where('email', 'ja-existe@cetem.gov.br')->count());
    }

    public function test_associacao_de_grupo_em_lote(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $grupo = Group::create(['name' => 'Editores']);
        $usuario = User::factory()->create(['email' => 'usuario1@cetem.gov.br', 'group_id' => null]);

        $csv = "email,grupo\n";
        $csv .= "usuario1@cetem.gov.br,Editores\n";
        $csv .= "naoexiste@cetem.gov.br,Editores\n";
        $csv .= "usuario1@cetem.gov.br,GrupoInexistente\n";

        $file = UploadedFile::fake()->createWithContent('grupos.csv', $csv);

        $response = $this->actingAs($admin)->post(route('admin.usuarios.grupo-lote.import'), ['csv' => $file]);

        $response->assertRedirect(route('admin.usuarios.grupo-lote.form'));
        $this->assertEquals($grupo->id, $usuario->fresh()->group_id);
    }

    public function test_usuario_nao_admin_nao_acessa_lote_de_usuarios(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get(route('admin.usuarios.lote.form'))->assertForbidden();
    }
}
