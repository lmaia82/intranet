<?php

namespace Tests\Feature;

use App\Models\Arquivo;
use App\Models\Pasta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OnlyOfficeAplicacoesTest extends TestCase
{
    use RefreshDatabase;

    public function test_documentos_retorna_json_com_arquivos_da_pasta_pessoal(): void
    {
        Storage::fake('arquivos');
        $user = User::factory()->create();
        $pasta = Pasta::create(['user_id' => $user->id, 'parent_id' => null, 'nome' => 'Meus Arquivos']);

        Storage::disk('arquivos')->put('uploads/doc.docx', 'conteudo');
        $arquivo = Arquivo::create([
            'pasta_id' => $pasta->id,
            'nome_original' => 'Relatório.docx',
            'caminho' => 'uploads/doc.docx',
            'extensao' => 'docx',
            'tamanho' => 8,
        ]);

        $response = $this->actingAs($user)->getJson(route('onlyoffice.aplicacoes.documentos'));

        $response->assertOk()
            ->assertJson([[
                'id' => $arquivo->id,
                'nome_original' => 'Relatório.docx',
                'extensao' => 'docx',
                'editor_url' => route('onlyoffice.editor', $arquivo),
            ]]);
    }

    public function test_documentos_retorna_lista_vazia_quando_nao_ha_arquivos(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson(route('onlyoffice.aplicacoes.documentos'));

        $response->assertOk()->assertJson([]);
    }

    public function test_visitante_nao_autenticado_nao_acessa_documentos(): void
    {
        $this->get(route('onlyoffice.aplicacoes.documentos'))->assertRedirect(route('login'));
    }
}
