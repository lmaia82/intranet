<?php

namespace Tests\Feature;

use App\Models\Arquivo;
use App\Models\Destaque;
use App\Models\Informativo;
use App\Models\Pasta;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RepositorioArquivoEmUsoTest extends TestCase
{
    use RefreshDatabase;

    private function arquivoEmPasta(Pasta $pasta, Sector $sector): Arquivo
    {
        Storage::fake('arquivos');
        Storage::disk('arquivos')->put('uploads/nota.pdf', 'conteudo');

        return Arquivo::create([
            'pasta_id' => $pasta->id,
            'nome_original' => 'nota.pdf',
            'caminho' => 'uploads/nota.pdf',
            'extensao' => 'pdf',
            'tamanho' => 100,
            'sector_id' => $sector->id,
            'is_private' => false,
        ]);
    }

    public function test_exclusao_de_arquivo_usado_por_destaque_e_bloqueada(): void
    {
        $sector = Sector::create(['sigla' => 'TI']);
        $user = User::factory()->create(['sector_id' => $sector->id]);
        $pasta = Pasta::create(['nome' => 'Pasta', 'sector_id' => $sector->id, 'is_private' => false]);
        $arquivo = $this->arquivoEmPasta($pasta, $sector);
        Destaque::create(['titulo' => 'Destaque X', 'arquivo_id' => $arquivo->id]);

        $response = $this->actingAs($user)->delete(route('repositorio.arquivos.destroy', $arquivo));

        $response->assertRedirect(route('repositorio.index', ['pasta' => $pasta->id]));
        $response->assertSessionHas('erro');
        $this->assertNotNull($arquivo->fresh());
        Storage::disk('arquivos')->assertExists('uploads/nota.pdf');
    }

    public function test_exclusao_de_arquivo_usado_por_informativo_e_bloqueada(): void
    {
        $sector = Sector::create(['sigla' => 'TI']);
        $user = User::factory()->create(['sector_id' => $sector->id]);
        $pasta = Pasta::create(['nome' => 'Pasta', 'sector_id' => $sector->id, 'is_private' => false]);
        $arquivo = $this->arquivoEmPasta($pasta, $sector);
        Informativo::create(['title' => 'Informativo X', 'content' => 'conteudo', 'arquivo_id' => $arquivo->id]);

        $response = $this->actingAs($user)->delete(route('repositorio.arquivos.destroy', $arquivo));

        $response->assertRedirect(route('repositorio.index', ['pasta' => $pasta->id]));
        $response->assertSessionHas('erro');
        $this->assertNotNull($arquivo->fresh());
        Storage::disk('arquivos')->assertExists('uploads/nota.pdf');
    }

    public function test_exclusao_de_arquivo_sem_uso_funciona_normalmente(): void
    {
        $sector = Sector::create(['sigla' => 'TI']);
        $user = User::factory()->create(['sector_id' => $sector->id]);
        $pasta = Pasta::create(['nome' => 'Pasta', 'sector_id' => $sector->id, 'is_private' => false]);
        $arquivo = $this->arquivoEmPasta($pasta, $sector);

        $response = $this->actingAs($user)->delete(route('repositorio.arquivos.destroy', $arquivo));

        $response->assertRedirect(route('repositorio.index', ['pasta' => $pasta->id]));
        $response->assertSessionHas('status');
        $this->assertNull($arquivo->fresh());
        Storage::disk('arquivos')->assertMissing('uploads/nota.pdf');
    }

    public function test_exclusao_de_pasta_com_arquivo_em_uso_e_bloqueada(): void
    {
        $sector = Sector::create(['sigla' => 'TI']);
        $user = User::factory()->create(['sector_id' => $sector->id]);
        $pasta = Pasta::create(['nome' => 'Pasta', 'sector_id' => $sector->id, 'is_private' => false]);
        $arquivo = $this->arquivoEmPasta($pasta, $sector);
        Destaque::create(['titulo' => 'Destaque X', 'arquivo_id' => $arquivo->id]);

        $response = $this->actingAs($user)->delete(route('repositorio.pastas.destroy', $pasta));

        $response->assertRedirect(route('repositorio.index'));
        $response->assertSessionHas('erro');
        $this->assertNotNull($pasta->fresh());
        $this->assertNotNull($arquivo->fresh());
        Storage::disk('arquivos')->assertExists('uploads/nota.pdf');
    }

    public function test_exclusao_de_pasta_sem_arquivos_em_uso_funciona_normalmente(): void
    {
        $sector = Sector::create(['sigla' => 'TI']);
        $user = User::factory()->create(['sector_id' => $sector->id]);
        $pasta = Pasta::create(['nome' => 'Pasta', 'sector_id' => $sector->id, 'is_private' => false]);
        $arquivo = $this->arquivoEmPasta($pasta, $sector);

        $response = $this->actingAs($user)->delete(route('repositorio.pastas.destroy', $pasta));

        $response->assertRedirect(route('repositorio.index'));
        $response->assertSessionHas('status');
        $this->assertNull($pasta->fresh());
        $this->assertNull($arquivo->fresh());
    }
}
