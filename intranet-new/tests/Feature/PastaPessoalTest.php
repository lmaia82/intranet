<?php

namespace Tests\Feature;

use App\Models\Arquivo;
use App\Models\Pasta;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PastaPessoalTest extends TestCase
{
    use RefreshDatabase;

    public function test_pasta_meus_arquivos_de_outro_usuario_nao_aparece_na_raiz(): void
    {
        $sector = Sector::create(['sigla' => 'TI']);
        $dono = User::factory()->create(['sector_id' => $sector->id]);
        $outro = User::factory()->create(['sector_id' => $sector->id]);

        $this->actingAs($dono)->get(route('repositorio.meus'));
        $pastaDoDono = Pasta::where('user_id', $dono->id)->firstOrFail();

        $response = $this->actingAs($outro)->get(route('repositorio.index'));

        $response->assertOk()
            ->assertDontSee(route('repositorio.index', ['pasta' => $pastaDoDono->id]), false);
    }

    public function test_dono_ve_sua_propria_pasta_meus_arquivos_na_raiz(): void
    {
        $sector = Sector::create(['sigla' => 'TI']);
        $dono = User::factory()->create(['sector_id' => $sector->id]);

        $this->actingAs($dono)->get(route('repositorio.meus'));
        $pasta = Pasta::where('user_id', $dono->id)->firstOrFail();

        $response = $this->actingAs($dono)->get(route('repositorio.index'));

        $response->assertOk()
            ->assertSee(route('repositorio.index', ['pasta' => $pasta->id]), false);
    }

    public function test_outro_usuario_nao_acessa_pasta_pessoal_diretamente_pela_url(): void
    {
        $sector = Sector::create(['sigla' => 'TI']);
        $dono = User::factory()->create(['sector_id' => $sector->id]);
        $outro = User::factory()->create(['sector_id' => $sector->id]);

        $pasta = Pasta::create(['nome' => 'Meus Arquivos', 'user_id' => $dono->id, 'sector_id' => $sector->id]);

        $this->actingAs($outro)->get(route('repositorio.index', ['pasta' => $pasta->id]))->assertForbidden();
    }

    public function test_admin_acessa_pasta_pessoal_de_outro_usuario(): void
    {
        $sector = Sector::create(['sigla' => 'TI']);
        $dono = User::factory()->create(['sector_id' => $sector->id]);
        $admin = User::factory()->create(['is_admin' => true]);

        $pasta = Pasta::create(['nome' => 'Meus Arquivos', 'user_id' => $dono->id, 'sector_id' => $sector->id]);

        $this->actingAs($admin)->get(route('repositorio.index', ['pasta' => $pasta->id]))->assertOk();
    }

    public function test_arquivo_dentro_da_pasta_pessoal_nao_e_visivel_para_outro_usuario_mesmo_publico(): void
    {
        Storage::fake('arquivos');
        $sector = Sector::create(['sigla' => 'TI']);
        $dono = User::factory()->create(['sector_id' => $sector->id]);
        $outro = User::factory()->create(['sector_id' => $sector->id]);

        $pasta = Pasta::create(['nome' => 'Meus Arquivos', 'user_id' => $dono->id, 'sector_id' => $sector->id]);
        Storage::disk('arquivos')->put('uploads/pessoal.docx', 'conteudo');
        $arquivo = Arquivo::create([
            'pasta_id' => $pasta->id,
            'nome_original' => 'pessoal.docx',
            'caminho' => 'uploads/pessoal.docx',
            'extensao' => 'docx',
            'tamanho' => 8,
            'is_private' => false,
        ]);

        $this->actingAs($outro)->get(route('repositorio.download', $arquivo))->assertForbidden();
        $this->actingAs($dono)->get(route('repositorio.download', $arquivo))->assertOk();
    }

    public function test_subpasta_criada_dentro_da_pasta_pessoal_herda_dono_e_fica_privada(): void
    {
        $sector = Sector::create(['sigla' => 'TI']);
        $dono = User::factory()->create(['sector_id' => $sector->id]);
        $outro = User::factory()->create(['sector_id' => $sector->id]);

        $pasta = Pasta::create(['nome' => 'Meus Arquivos', 'user_id' => $dono->id, 'sector_id' => $sector->id]);

        $this->actingAs($dono)->post(route('repositorio.pastas.store'), [
            'nome' => 'Projetos',
            'parent_id' => $pasta->id,
            'sector_id' => $sector->id,
        ]);

        $subpasta = Pasta::where('nome', 'Projetos')->first();
        $this->assertEquals($dono->id, $subpasta->user_id);

        $this->actingAs($outro)->get(route('repositorio.index', ['pasta' => $subpasta->id]))->assertForbidden();
    }

    public function test_arquivo_pessoal_publico_nao_aparece_no_widget_de_documentos_publicos_da_home(): void
    {
        Storage::fake('arquivos');
        $sector = Sector::create(['sigla' => 'TI']);
        $dono = User::factory()->create(['sector_id' => $sector->id]);
        $outro = User::factory()->create(['sector_id' => $sector->id]);

        $pasta = Pasta::create(['nome' => 'Meus Arquivos', 'user_id' => $dono->id, 'sector_id' => $sector->id]);
        Storage::disk('arquivos')->put('uploads/pessoal.docx', 'conteudo');
        Arquivo::create([
            'pasta_id' => $pasta->id,
            'nome_original' => 'DocumentoPessoalDoDono.docx',
            'caminho' => 'uploads/pessoal.docx',
            'extensao' => 'docx',
            'tamanho' => 8,
            'is_private' => false,
            'data' => now()->toDateString(),
        ]);

        $response = $this->actingAs($outro)->get(route('dashboard'));

        $response->assertOk()->assertDontSee('DocumentoPessoalDoDono.docx');
    }

    public function test_outro_usuario_nao_abre_editor_de_documento_criado_pelas_aplicacoes(): void
    {
        Storage::fake('arquivos');
        $sector = Sector::create(['sigla' => 'TI']);
        $dono = User::factory()->create(['sector_id' => $sector->id]);
        $outro = User::factory()->create(['sector_id' => $sector->id]);

        $this->actingAs($dono)->post(route('onlyoffice.criar'), [
            'tipo' => 'docx',
            'titulo' => 'DocumentoDoDono',
        ]);

        $arquivo = Arquivo::where('nome_original', 'DocumentoDoDono.docx')->firstOrFail();

        $this->actingAs($outro)->get(route('onlyoffice.editor', $arquivo))->assertForbidden();
        $this->actingAs($dono)->get(route('onlyoffice.editor', $arquivo))->assertOk();
    }
}
