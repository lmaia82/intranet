<?php

namespace Tests\Feature;

use App\Models\Destaque;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DestaqueTest extends TestCase
{
    use RefreshDatabase;

    public function test_pode_criar_destaque_com_imagem(): void
    {
        Storage::fake('arquivos');
        $sector = \App\Models\Sector::create(['sigla' => 'TI']);
        $user = User::factory()->create(['sector_id' => $sector->id]);

        $this->actingAs($user)->post(route('destaques.store'), [
            'titulo' => 'Campanha de vacinação',
            'imagem' => UploadedFile::fake()->image('banner.png', 1600, 500),
            'link' => 'https://cetem.gov.br',
            'ordem' => 1,
            'ativo' => '1',
            'inicio_em' => now()->format('Y-m-d\TH:i'),
            'fim_em' => now()->addDays(30)->format('Y-m-d\TH:i'),
        ])->assertRedirect(route('destaques.index'));

        $this->assertDatabaseHas('destaques', [
            'titulo' => 'Campanha de vacinação',
            'link' => 'https://cetem.gov.br',
            'ativo' => true,
            'sector_id' => $sector->id,
        ]);

        $destaque = Destaque::first();
        $this->assertNotNull($destaque->arquivo_id);
        Storage::disk('arquivos')->assertExists($destaque->arquivo->caminho);
    }

    public function test_imagem_do_destaque_e_acessivel_sem_login(): void
    {
        Storage::fake('arquivos');
        $sector = \App\Models\Sector::create(['sigla' => 'TI']);
        $user = User::factory()->create(['sector_id' => $sector->id]);

        $this->actingAs($user)->post(route('destaques.store'), [
            'titulo' => 'Campanha',
            'imagem' => UploadedFile::fake()->image('banner.png', 1600, 500),
            'inicio_em' => now()->format('Y-m-d\TH:i'),
            'fim_em' => now()->addDays(30)->format('Y-m-d\TH:i'),
        ]);

        $destaque = Destaque::first();

        $this->assertStringContainsString('/publico', $destaque->imagemUrl());

        // Sem login (guest), sem seguir redirecionamento: precisa dar 200,
        // nunca um redirect para /login (o que poluiria a "intended url").
        $this->get($destaque->imagemUrl())->assertOk();
    }

    public function test_arquivo_privado_nao_e_servido_pela_rota_publica(): void
    {
        Storage::fake('arquivos');
        Storage::disk('arquivos')->put('uploads/privado.png', 'conteudo');
        $arquivo = \App\Models\Arquivo::create([
            'nome_original' => 'privado.png',
            'caminho' => 'uploads/privado.png',
            'extensao' => 'png',
            'tamanho' => 10,
            'is_private' => true,
        ]);

        $this->get(route('repositorio.arquivos.visualizar-publico', $arquivo))->assertForbidden();
    }

    public function test_imagem_e_obrigatoria_na_criacao(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('destaques.store'), [
            'titulo' => 'Sem imagem',
        ])->assertSessionHasErrors('imagem');
    }

    public function test_criar_destaque_sem_setor_falha(): void
    {
        Storage::fake('arquivos');
        $user = User::factory()->create(['sector_id' => null]);

        $response = $this->actingAs($user)->post(route('destaques.store'), [
            'titulo' => 'Campanha de vacinação',
            'imagem' => UploadedFile::fake()->image('banner.png', 1600, 500),
            'inicio_em' => now()->format('Y-m-d\TH:i'),
            'fim_em' => now()->addDays(30)->format('Y-m-d\TH:i'),
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('destaques', 0);
    }

    public function test_excluir_destaque_remove_a_imagem_do_minio(): void
    {
        Storage::fake('arquivos');
        $sector = \App\Models\Sector::create(['sigla' => 'TI']);
        $user = User::factory()->create(['sector_id' => $sector->id]);

        $this->actingAs($user)->post(route('destaques.store'), [
            'titulo' => 'Campanha',
            'imagem' => UploadedFile::fake()->image('banner.png', 1600, 500),
            'inicio_em' => now()->format('Y-m-d\TH:i'),
            'fim_em' => now()->addDays(30)->format('Y-m-d\TH:i'),
        ]);

        $destaque = Destaque::first();
        $caminho = $destaque->arquivo->caminho;
        $arquivoId = $destaque->arquivo_id;

        $this->actingAs($user)->delete(route('destaques.destroy', $destaque));

        $this->assertDatabaseMissing('arquivos', ['id' => $arquivoId]);
        Storage::disk('arquivos')->assertMissing($caminho);
    }

    public function test_trocar_imagem_na_edicao_remove_a_anterior_do_minio(): void
    {
        Storage::fake('arquivos');
        $sector = \App\Models\Sector::create(['sigla' => 'TI']);
        $user = User::factory()->create(['sector_id' => $sector->id]);

        $this->actingAs($user)->post(route('destaques.store'), [
            'titulo' => 'Campanha',
            'imagem' => UploadedFile::fake()->image('banner.png', 1600, 500),
            'inicio_em' => now()->format('Y-m-d\TH:i'),
            'fim_em' => now()->addDays(30)->format('Y-m-d\TH:i'),
        ]);

        $destaque = Destaque::first();
        $caminhoAntigo = $destaque->arquivo->caminho;
        $arquivoIdAntigo = $destaque->arquivo_id;

        $this->actingAs($user)->put(route('destaques.update', $destaque), [
            'titulo' => 'Campanha',
            'imagem' => UploadedFile::fake()->image('nova.png', 1600, 500),
            'inicio_em' => now()->format('Y-m-d\TH:i'),
            'fim_em' => now()->addDays(30)->format('Y-m-d\TH:i'),
        ]);

        $destaque->refresh();
        $this->assertNotEquals($arquivoIdAntigo, $destaque->arquivo_id);
        $this->assertDatabaseMissing('arquivos', ['id' => $arquivoIdAntigo]);
        Storage::disk('arquivos')->assertMissing($caminhoAntigo);
        Storage::disk('arquivos')->assertExists($destaque->arquivo->caminho);
    }

    public function test_pode_editar_destaque_sem_trocar_imagem(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $destaque = Destaque::create([
            'titulo' => 'Original',
            'imagem' => 'destaques/original.jpg',
            'ordem' => 0,
            'ativo' => true,
            'inicio_em' => now(),
            'fim_em' => now()->addDays(10),
        ]);

        $this->actingAs($user)->put(route('destaques.update', $destaque), [
            'titulo' => 'Editado',
            'ordem' => 2,
            'ativo' => '1',
            'inicio_em' => now()->format('Y-m-d\TH:i'),
            'fim_em' => now()->addDays(30)->format('Y-m-d\TH:i'),
        ])->assertRedirect(route('destaques.index'));

        $this->assertDatabaseHas('destaques', ['titulo' => 'Editado', 'imagem' => 'destaques/original.jpg']);
    }

    public function test_pode_remover_destaque(): void
    {
        $user = User::factory()->create();
        $destaque = Destaque::create([
            'titulo' => 'Para remover',
            'imagem' => 'destaques/remover.jpg',
            'ordem' => 0,
            'ativo' => true,
        ]);

        $this->actingAs($user)->delete(route('destaques.destroy', $destaque))
            ->assertRedirect(route('destaques.index'));

        $this->assertDatabaseMissing('destaques', ['id' => $destaque->id]);
    }

    public function test_scope_ativos_ignora_inativos_e_ordena(): void
    {
        Destaque::create(['titulo' => 'B', 'imagem' => 'b.jpg', 'ordem' => 2, 'ativo' => true]);
        Destaque::create(['titulo' => 'Inativo', 'imagem' => 'i.jpg', 'ordem' => 0, 'ativo' => false]);
        Destaque::create(['titulo' => 'A', 'imagem' => 'a.jpg', 'ordem' => 1, 'ativo' => true]);

        $ativos = Destaque::ativos()->pluck('titulo')->all();

        $this->assertEquals(['A', 'B'], $ativos);
    }

    public function test_scope_ativos_ignora_destaques_fora_do_periodo(): void
    {
        Destaque::create([
            'titulo' => 'Ainda não começou',
            'imagem' => 'futuro.jpg',
            'ordem' => 0,
            'ativo' => true,
            'inicio_em' => now()->addDays(5),
            'fim_em' => now()->addDays(10),
        ]);
        Destaque::create([
            'titulo' => 'Já expirou',
            'imagem' => 'expirado.jpg',
            'ordem' => 0,
            'ativo' => true,
            'inicio_em' => now()->subDays(10),
            'fim_em' => now()->subDay(),
        ]);
        Destaque::create([
            'titulo' => 'Dentro do período',
            'imagem' => 'atual.jpg',
            'ordem' => 0,
            'ativo' => true,
            'inicio_em' => now()->subDay(),
            'fim_em' => now()->addDay(),
        ]);
        Destaque::create([
            'titulo' => 'Sem periodo definido',
            'imagem' => 'sem-periodo.jpg',
            'ordem' => 0,
            'ativo' => true,
        ]);

        $ativos = Destaque::ativos()->pluck('titulo')->all();

        $this->assertEqualsCanonicalizing(['Dentro do período', 'Sem periodo definido'], $ativos);
    }

    public function test_expirado_indica_destaques_com_fim_no_passado(): void
    {
        $expirado = Destaque::create([
            'titulo' => 'Expirado',
            'imagem' => 'e.jpg',
            'ordem' => 0,
            'ativo' => true,
            'inicio_em' => now()->subDays(5),
            'fim_em' => now()->subDay(),
        ]);
        $vigente = Destaque::create([
            'titulo' => 'Vigente',
            'imagem' => 'v.jpg',
            'ordem' => 0,
            'ativo' => true,
            'inicio_em' => now()->subDay(),
            'fim_em' => now()->addDay(),
        ]);

        $this->assertTrue($expirado->expirado());
        $this->assertFalse($vigente->expirado());
    }

    public function test_dashboard_exibe_carrossel_com_destaque_ativo(): void
    {
        $user = User::factory()->create();
        Destaque::create(['titulo' => 'Aviso Importante', 'imagem' => 'destaques/x.jpg', 'ordem' => 0, 'ativo' => true]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('destaques/x.jpg', false);
    }

    public function test_usuario_sem_permissao_nao_acessa_tela_de_destaques(): void
    {
        $user = User::factory()->create(['group_id' => null]);

        $this->actingAs($user)->get(route('destaques.index'))->assertForbidden();
    }
}
