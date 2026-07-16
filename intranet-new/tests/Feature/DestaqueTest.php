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
        Storage::fake('public');
        $user = User::factory()->create();

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
        ]);

        $destaque = Destaque::first();
        Storage::disk('public')->assertExists($destaque->imagem);
    }

    public function test_imagem_e_obrigatoria_na_criacao(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('destaques.store'), [
            'titulo' => 'Sem imagem',
        ])->assertSessionHasErrors('imagem');
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
