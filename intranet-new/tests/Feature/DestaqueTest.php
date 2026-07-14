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
        ]);

        $this->actingAs($user)->put(route('destaques.update', $destaque), [
            'titulo' => 'Editado',
            'ordem' => 2,
            'ativo' => '1',
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
