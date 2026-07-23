<?php

namespace Tests\Feature;

use App\Models\Arquivo;
use App\Models\Evento;
use App\Models\EventoGravado;
use App\Models\Group;
use App\Models\Informativo;
use App\Models\Permission;
use App\Models\Sector;
use App\Models\Telefone;
use App\Models\Tutorial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuscaTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_encontra_item_de_cada_modulo(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'group_id' => null]);
        $sector = Sector::create(['sigla' => 'TI']);

        Telefone::create(['nome' => 'Fulano Buscatermo', 'telefone' => '1234', 'cargo' => 'Analista', 'sector_id' => $sector->id]);
        Informativo::create(['title' => 'Aviso Buscatermo', 'content' => 'x', 'published_at' => now()]);
        Evento::create(['title' => 'Evento Buscatermo', 'local' => 'Sala', 'dt_start' => '2026-01-01']);
        EventoGravado::create(['titulo' => 'Gravado Buscatermo', 'data' => '2026-01-01', 'youtube_url' => 'https://youtu.be/x']);
        Tutorial::create(['titulo' => 'Tutorial Buscatermo', 'data' => '2026-01-01', 'youtube_url' => 'https://youtu.be/y']);

        $this->actingAs($admin)->get(route('busca.index', ['q' => 'Buscatermo']))
            ->assertOk()
            ->assertSee('Fulano Buscatermo')
            ->assertSee('Aviso Buscatermo')
            ->assertSee('Evento Buscatermo')
            ->assertSee('Gravado Buscatermo')
            ->assertSee('Tutorial Buscatermo');
    }

    public function test_usuario_so_ve_resultados_de_modulos_que_tem_permissao(): void
    {
        $permissao = Permission::where('key', 'ramais.ver')->first();
        $grupo = Group::create(['name' => 'So Ramais']);
        $grupo->permissions()->attach($permissao);
        $user = User::factory()->create(['group_id' => $grupo->id]);
        $sector = Sector::create(['sigla' => 'TI']);

        Telefone::create(['nome' => 'Ciclano Buscatermo', 'telefone' => '5678', 'cargo' => 'Técnico', 'sector_id' => $sector->id]);
        Tutorial::create(['titulo' => 'Tutorial Buscatermo', 'data' => '2026-01-01', 'youtube_url' => 'https://youtu.be/y']);

        $this->actingAs($user)->get(route('busca.index', ['q' => 'Buscatermo']))
            ->assertOk()
            ->assertSee('Ciclano Buscatermo')
            ->assertDontSee('Tutorial Buscatermo');
    }

    public function test_informativo_restrito_a_outro_setor_nao_aparece_na_busca(): void
    {
        $setorA = Sector::create(['sigla' => 'Setor A']);
        $setorB = Sector::create(['sigla' => 'Setor B']);

        $permissao = Permission::where('key', 'informativos.ver')->first();
        $grupo = Group::create(['name' => 'Leitores Informativos']);
        $grupo->permissions()->attach($permissao);
        $user = User::factory()->create(['group_id' => $grupo->id, 'sector_id' => $setorA->id]);

        Informativo::create([
            'title' => 'Restrito Buscatermo',
            'content' => 'x',
            'sector_id' => $setorB->id,
            'is_private' => true,
            'published_at' => now(),
        ]);

        $this->actingAs($user)->get(route('busca.index', ['q' => 'Buscatermo']))
            ->assertOk()
            ->assertDontSee('Restrito Buscatermo');
    }

    public function test_informativo_restrito_a_coordenacao_aparece_para_usuario_do_servico_subordinado(): void
    {
        $coordenacao = Sector::create(['sigla' => 'COADM']);
        $servico = Sector::create(['sigla' => 'SECOF', 'parent_id' => $coordenacao->id]);

        $permissao = Permission::where('key', 'informativos.ver')->first();
        $grupo = Group::create(['name' => 'Leitores Informativos Hierarquia']);
        $grupo->permissions()->attach($permissao);
        $user = User::factory()->create(['group_id' => $grupo->id, 'sector_id' => $servico->id]);

        Informativo::create([
            'title' => 'Restrito Da Coordenacao Buscatermo',
            'content' => 'x',
            'sector_id' => $coordenacao->id,
            'is_private' => true,
            'published_at' => now(),
        ]);

        $this->actingAs($user)->get(route('busca.index', ['q' => 'Buscatermo']))
            ->assertOk()
            ->assertSee('Restrito Da Coordenacao Buscatermo');
    }

    public function test_encontra_arquivo_pelo_conteudo_ocr(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'group_id' => null]);
        $sector = Sector::create(['sigla' => 'TI']);

        Arquivo::create([
            'nome_original' => 'documento-sem-relacao.pdf',
            'caminho' => 'uploads/documento-sem-relacao.pdf',
            'extensao' => 'pdf',
            'tamanho' => 100,
            'sector_id' => $sector->id,
            'is_private' => false,
            'conteudo_ocr' => 'Este texto foi extraído via OCR e contém PalavraChaveOcrBuscatermo.',
        ]);

        $this->actingAs($admin)->get(route('busca.index', ['q' => 'PalavraChaveOcrBuscatermo']))
            ->assertOk()
            ->assertSee('documento-sem-relacao.pdf')
            ->assertSee('encontrado no conteúdo do PDF');
    }

    public function test_busca_sem_termo_nao_executa_query(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('busca.index'))
            ->assertOk()
            ->assertSee('Digite um termo');
    }
}
