<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Permission;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganogramaTest extends TestCase
{
    use RefreshDatabase;

    private function usuarioComPermissao(): User
    {
        $permissao = Permission::where('key', 'organograma.ver')->first();
        $grupo = Group::create(['name' => 'Ve Organograma ' . uniqid()]);
        $grupo->permissions()->attach($permissao);

        return User::factory()->create(['group_id' => $grupo->id]);
    }

    public function test_pagina_mostra_diretoria_coordenacoes_e_servicos(): void
    {
        $diretoria = Sector::create(['sigla' => 'DIRETORIA', 'nome' => 'Diretoria']);
        $coordenacao = Sector::create(['sigla' => 'COADM', 'nome' => 'Coordenação de Administração']);
        Sector::create(['sigla' => 'SECOF', 'nome' => 'Serviço de Contabilidade', 'parent_id' => $coordenacao->id]);

        $user = $this->usuarioComPermissao();

        $this->actingAs($user)->get(route('organograma.index'))
            ->assertOk()
            ->assertSeeInOrder(['Diretoria', 'COADM', 'Coordenação de Administração', 'SECOF']);
    }

    public function test_setores_sem_coordenacao_e_diferentes_de_diretoria_aparecem_como_coordenacao(): void
    {
        Sector::create(['sigla' => 'SEIN', 'nome' => 'Serviço de Informática']);

        $user = $this->usuarioComPermissao();

        $this->actingAs($user)->get(route('organograma.index'))
            ->assertOk()
            ->assertSee('SEIN');
    }

    public function test_usuario_sem_permissao_nao_acessa_organograma(): void
    {
        $user = User::factory()->create(['group_id' => null]);

        $this->actingAs($user)->get(route('organograma.index'))->assertForbidden();
    }

    public function test_link_do_organograma_aparece_no_menu_para_quem_tem_permissao(): void
    {
        $user = $this->usuarioComPermissao();

        $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertSee('Organograma');
    }

    public function test_link_do_organograma_nao_aparece_para_quem_nao_tem_permissao(): void
    {
        $user = User::factory()->create(['group_id' => null]);

        $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertDontSee('Organograma');
    }

    public function test_pagina_carrega_mesmo_sem_setor_diretoria_cadastrado(): void
    {
        Sector::create(['sigla' => 'COADM']);
        $user = $this->usuarioComPermissao();

        $this->actingAs($user)->get(route('organograma.index'))->assertOk()->assertSee('COADM');
    }

    public function test_ctc_aparece_como_elemento_fixo_ao_lado_da_diretoria(): void
    {
        Sector::create(['sigla' => 'DIRETORIA', 'nome' => 'Diretoria']);
        $user = $this->usuarioComPermissao();

        $this->actingAs($user)->get(route('organograma.index'))
            ->assertOk()
            ->assertSee('CTC - Conselho Técnico Científico');
    }

    public function test_lista_de_colaboradores_mostra_nome_e_email_em_caixa_baixa(): void
    {
        $coordenacao = Sector::create(['sigla' => 'COADM', 'nome' => 'Coordenação de Administração']);
        $servico = Sector::create(['sigla' => 'SECOF', 'nome' => 'Serviço de Contabilidade', 'parent_id' => $coordenacao->id]);

        $daCoordenacao = User::factory()->create(['name' => 'Fulano da Coordenação', 'email' => 'FULANO@CETEM.GOV.BR', 'sector_id' => $coordenacao->id, 'ad_guid' => 'guid-1']);
        $doServico = User::factory()->create(['name' => 'Ciclano do Serviço', 'email' => 'CICLANO@CETEM.GOV.BR', 'sector_id' => $servico->id, 'ad_guid' => 'guid-2']);

        $user = $this->usuarioComPermissao();

        $this->actingAs($user)->get(route('organograma.index'))
            ->assertOk()
            ->assertSee('Fulano da Coordenação')
            ->assertSee('fulano@cetem.gov.br')
            ->assertDontSee('FULANO@CETEM.GOV.BR')
            ->assertSee('Ciclano do Serviço')
            ->assertSee('ciclano@cetem.gov.br');
    }

    public function test_lista_de_colaboradores_nao_traz_usuario_inativo(): void
    {
        $coordenacao = Sector::create(['sigla' => 'COADM', 'nome' => 'Coordenação de Administração']);

        User::factory()->create(['name' => 'Usuario Ativo', 'sector_id' => $coordenacao->id, 'is_active' => true, 'ad_guid' => 'guid-ativo']);
        User::factory()->create(['name' => 'Usuario Inativo', 'sector_id' => $coordenacao->id, 'is_active' => false, 'ad_guid' => 'guid-inativo']);

        $user = $this->usuarioComPermissao();

        $this->actingAs($user)->get(route('organograma.index'))
            ->assertOk()
            ->assertSee('Usuario Ativo')
            ->assertDontSee('Usuario Inativo');
    }

    public function test_lista_de_colaboradores_nao_traz_usuario_somente_da_intranet(): void
    {
        $coordenacao = Sector::create(['sigla' => 'COADM', 'nome' => 'Coordenação de Administração']);

        User::factory()->create(['name' => 'Usuario Do Ad', 'sector_id' => $coordenacao->id, 'ad_guid' => 'guid-1']);
        User::factory()->create(['name' => 'Usuario So Intranet', 'sector_id' => $coordenacao->id, 'ad_guid' => null]);

        $user = $this->usuarioComPermissao();

        $this->actingAs($user)->get(route('organograma.index'))
            ->assertOk()
            ->assertSee('Usuario Do Ad')
            ->assertDontSee('Usuario So Intranet');
    }

    public function test_lista_de_colaboradores_nao_traz_usuario_vinculado_so_pelo_setor_do_ad(): void
    {
        $coordenacao = Sector::create(['sigla' => 'COADM', 'nome' => 'Coordenação de Administração']);

        User::factory()->create(['name' => 'Vinculado Via Ad', 'sector_id' => null, 'ad_setor' => 'COADM']);

        $user = $this->usuarioComPermissao();

        $this->actingAs($user)->get(route('organograma.index'))
            ->assertOk()
            ->assertDontSee('Vinculado Via Ad');
    }

    public function test_lista_de_colaboradores_mostra_mensagem_quando_setor_nao_tem_ninguem_vinculado(): void
    {
        Sector::create(['sigla' => 'COADM', 'nome' => 'Coordenação de Administração']);

        $user = $this->usuarioComPermissao();

        $this->actingAs($user)->get(route('organograma.index'))
            ->assertOk()
            ->assertSee('Nenhum usuário vinculado a este setor.');
    }
}
