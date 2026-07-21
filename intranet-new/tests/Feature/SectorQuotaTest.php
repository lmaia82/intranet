<?php

namespace Tests\Feature;

use App\Models\Arquivo;
use App\Models\Pasta;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SectorQuotaTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pode_definir_cota_do_setor_em_mb(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $sector = Sector::create(['sigla' => 'TI']);

        $this->actingAs($admin)->put(route('admin.setores.update', $sector), [
            'sigla' => 'TI',
            'quota_mb' => 10,
        ])->assertRedirect(route('admin.setores'));

        $this->assertEquals(10 * 1048576, $sector->fresh()->quota_bytes);
    }

    public function test_setor_sem_cota_definida_e_ilimitado(): void
    {
        $sector = Sector::create(['sigla' => 'TI']);

        $this->assertNull($sector->percentualUso());
        $this->assertFalse($sector->quotaExcedida(999999999));
        $this->assertEquals('Sem limite', $sector->quotaFormatada());
    }

    public function test_uso_bytes_soma_arquivos_do_setor(): void
    {
        $sector = Sector::create(['sigla' => 'TI']);
        $outroSector = Sector::create(['sigla' => 'RH']);

        Arquivo::create(['nome_original' => 'a.pdf', 'caminho' => 'a.pdf', 'extensao' => 'pdf', 'tamanho' => 1000, 'sector_id' => $sector->id]);
        Arquivo::create(['nome_original' => 'b.pdf', 'caminho' => 'b.pdf', 'extensao' => 'pdf', 'tamanho' => 2000, 'sector_id' => $sector->id]);
        Arquivo::create(['nome_original' => 'c.pdf', 'caminho' => 'c.pdf', 'extensao' => 'pdf', 'tamanho' => 5000, 'sector_id' => $outroSector->id]);

        $this->assertEquals(3000, $sector->usoBytes());
        $this->assertEquals(5000, $outroSector->usoBytes());
    }

    public function test_upload_bloqueado_quando_excede_cota_do_setor(): void
    {
        $user = User::factory()->create();
        $sector = Sector::create(['sigla' => 'TI', 'quota_bytes' => 1024]);
        $pasta = Pasta::create(['nome' => 'Notas', 'sector_id' => $sector->id, 'is_private' => false]);

        Arquivo::create(['nome_original' => 'existente.pdf', 'caminho' => 'existente.pdf', 'extensao' => 'pdf', 'tamanho' => 900, 'sector_id' => $sector->id]);

        $file = UploadedFile::fake()->create('novo.pdf', 200);

        $response = $this->actingAs($user)->post(route('repositorio.arquivos.store'), [
            'arquivo' => $file,
            'sector_id' => $sector->id,
            'pasta_id' => $pasta->id,
        ]);

        $response->assertSessionHasErrors('arquivo');
        $this->assertEquals(1, Arquivo::where('sector_id', $sector->id)->count());
    }

    public function test_upload_permitido_dentro_da_cota_do_setor(): void
    {
        Storage::fake('arquivos');

        $user = User::factory()->create();
        $sector = Sector::create(['sigla' => 'TI', 'quota_bytes' => 1048576]);
        $pasta = Pasta::create(['nome' => 'Notas', 'sector_id' => $sector->id, 'is_private' => false]);

        $file = UploadedFile::fake()->create('novo.pdf', 100);

        $response = $this->actingAs($user)->post(route('repositorio.arquivos.store'), [
            'arquivo' => $file,
            'sector_id' => $sector->id,
            'pasta_id' => $pasta->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors();
        $this->assertEquals(1, Arquivo::where('sector_id', $sector->id)->count());
    }

    public function test_upload_sem_informar_setor_falha_validacao(): void
    {
        $user = User::factory()->create();

        $file = UploadedFile::fake()->create('novo.pdf', 100);

        $response = $this->actingAs($user)->post(route('repositorio.arquivos.store'), [
            'arquivo' => $file,
        ]);

        $response->assertSessionHasErrors('sector_id');
    }

    public function test_pasta_publica_e_visivel_a_qualquer_usuario(): void
    {
        $sector = Sector::create(['sigla' => 'TI']);
        $outroSetor = Sector::create(['sigla' => 'RH']);
        $user = User::factory()->create(['sector_id' => $outroSetor->id]);

        $pasta = \App\Models\Pasta::create(['nome' => 'Pública', 'sector_id' => $sector->id, 'is_private' => false]);

        $this->actingAs($user)->get(route('repositorio.index'))->assertOk()->assertSee('Pública');
    }

    public function test_pasta_restrita_ao_setor_e_invisivel_para_outro_setor(): void
    {
        $sector = Sector::create(['sigla' => 'TI']);
        $outroSetor = Sector::create(['sigla' => 'RH']);
        $user = User::factory()->create(['sector_id' => $outroSetor->id]);

        $pasta = \App\Models\Pasta::create(['nome' => 'Restrita TI', 'sector_id' => $sector->id, 'is_private' => true]);

        $this->actingAs($user)->get(route('repositorio.index'))->assertOk()->assertDontSee('Restrita TI');
        $this->actingAs($user)->get(route('repositorio.index', ['pasta' => $pasta->id]))->assertForbidden();
    }

    public function test_pasta_restrita_ao_setor_e_visivel_para_mesmo_setor(): void
    {
        $sector = Sector::create(['sigla' => 'TI']);
        $user = User::factory()->create(['sector_id' => $sector->id]);

        \App\Models\Pasta::create(['nome' => 'Restrita TI', 'sector_id' => $sector->id, 'is_private' => true]);

        $this->actingAs($user)->get(route('repositorio.index'))->assertOk()->assertSee('Restrita TI');
    }

    public function test_admin_ve_pastas_restritas_de_qualquer_setor(): void
    {
        $sector = Sector::create(['sigla' => 'TI']);
        $admin = User::factory()->create(['is_admin' => true]);

        \App\Models\Pasta::create(['nome' => 'Restrita TI', 'sector_id' => $sector->id, 'is_private' => true]);

        $this->actingAs($admin)->get(route('repositorio.index'))->assertOk()->assertSee('Restrita TI');
    }

    public function test_download_de_arquivo_restrito_bloqueado_para_outro_setor(): void
    {
        Storage::fake('arquivos');

        $sector = Sector::create(['sigla' => 'TI']);
        $outroSetor = Sector::create(['sigla' => 'RH']);
        $user = User::factory()->create(['sector_id' => $outroSetor->id]);

        Storage::disk('arquivos')->put('uploads/restrito.pdf', 'conteudo');
        $arquivo = Arquivo::create([
            'nome_original' => 'restrito.pdf',
            'caminho' => 'uploads/restrito.pdf',
            'extensao' => 'pdf',
            'tamanho' => 8,
            'sector_id' => $sector->id,
            'is_private' => true,
        ]);

        $this->actingAs($user)->get(route('repositorio.download', $arquivo))->assertForbidden();
    }

    public function test_dashboard_de_armazenamento_renderiza_para_admin(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Sector::create(['sigla' => 'TI', 'quota_bytes' => 1048576]);
        Sector::create(['sigla' => 'RH']);

        $this->actingAs($admin)->get(route('admin.armazenamento'))
            ->assertOk()
            ->assertSee('TI')
            ->assertSee('RH')
            ->assertSee('Sem cota definida');
    }

    public function test_usuario_nao_admin_nao_acessa_dashboard_de_armazenamento(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get(route('admin.armazenamento'))->assertForbidden();
    }
}
