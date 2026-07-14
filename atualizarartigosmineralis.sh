#!/bin/bash
set -e
cd ~/projetos/intranet
rm -f intranet-new/resources/views/artigos/_form.blade.php
rm -f intranet-new/resources/views/artigos/create.blade.php
rm -f intranet-new/resources/views/artigos/edit.blade.php
rm -f intranet-new/resources/views/artigos/lote.blade.php
rm -f intranet-new/resources/views/artigos/show.blade.php

cat > PLANEJAMENTO.md <<'FILE_EOF_1'
# Planejamento — Modernização da Intranet

Reescrita completa da intranet (framework PHP próprio "Diamond") usando
Laravel. O código legado (`intranet-legacy-slim/` e `lib/`) serviu só como
referência de regras de negócio — nunca foi executado — e já foi removido do
repositório depois de todas as regras terem sido portadas. Fica apenas
`intranet-legacy-slim/bck_intranet.sql`, o dump com os dados reais de
produção, guardado até a migração de dados (ver Pendências abaixo).

Stack: Laravel + Blade/Livewire, rodando via Docker Compose na VM Ubuntu
(`docker-compose.yml` na raiz). Autenticação local (Breeze) por enquanto;
integração com AD/LDAP fica para o final, depois de todas as telas prontas.

## Status das telas

| Tela | Status |
| --- | --- |
| Ramais / Telefones | ✅ Pronta (CRUD completo) |
| Informativos / Mural de Avisos | ✅ Pronta (CRUD + upload de imagem) |
| Agenda / Eventos | ✅ Pronta (CRUD completo) |
| Repositório de Arquivos | ✅ Pronta (pastas, upload/download, MinIO) |
| Artigos | ✅ Pronta (apresentação + links para o Mineralis e o Master) |
| Painel de Administração | ✅ Pronta (setores, usuários, estatísticas) |
| Autenticação via AD/LDAP | ⬜ Pendente (substituirá o Breeze) |

- **Identidade visual CETEM**: ✅ Aplicada (logo oficial, cores institucionais azul #0052CC/laranja #F4A000, tipografia Calibri/Arial). Falta ainda ajustar telas de login/registro (Breeze padrão).

- **Notificação por e-mail de Informativos**: ✅ Pronta. Ao publicar um
  Informativo, o usuário pode marcar a opção "Enviar notificação por
  e-mail ao publicar" (não é automático) para disparar um e-mail (template
  com identidade visual CETEM) para todos os usuários ou, se um setor for
  selecionado, apenas para os usuários daquele setor (campo `sector_id` em
  `users`, ainda provisório até a integração com AD/LDAP). Cada envio é
  registrado na tabela `informativo_envios` (e-mail + data/hora), exibido
  na tela do Informativo. O botão "Reenviar e-mails" leva a uma tela onde
  a lista de destinatários vem pré-carregada pelo setor do informativo (ou
  todos os usuários), mas fica num campo de texto totalmente editável —
  dá pra adicionar e-mails avulsos (mesmo de quem não é usuário cadastrado)
  ou remover linhas antes de confirmar o reenvio. Testado localmente via
  Mailpit.

- **Eventos gravados**: ✅ Pronta. Nova seção na tela de Agenda/Eventos,
  abaixo dos eventos anteriores, listando gravações (data + título com
  link direto para o vídeo no YouTube, aberto em nova aba). CRUD próprio
  (`evento_gravados`) e cadastro em lote via CSV (mesmo padrão do
  cadastro em lote de Artigos).

- **Cadastro em lote de Ramais**: ✅ Pronta. Tela de Ramais/Telefones
  ganhou opção "Cadastro em lote" (mesmo padrão de Artigos/Eventos
  gravados): CSV com nome, telefone, setor (deve bater com um setor já
  cadastrado), e-mail e cargo.

- **Grupos de permissão**: ✅ Pronta. "Tela Inicial" renomeada (era
  "Dashboard"). Admin > Grupos permite criar grupos com permissões
  flexíveis de "Ver" e "Criar/editar" por tela (Ramais, Informativos,
  Agenda, Repositório) — ex.: um grupo só lê Informativos, outro pode
  publicá-los. Usuário sem grupo não acessa nada (exceto
  administradores, que ignoram a checagem). Cada usuário tem um único
  grupo (`users.group_id`), ajustável individualmente ou em lote (CSV
  e-mail+grupo). Cadastro de usuários também ganhou lote (CSV com
  nome/e-mail/senha/setor/grupo/admin). A migração cria um grupo
  padrão "Colaboradores" com acesso total e migra todos os usuários
  existentes para ele, então nada quebra após o `migrate` — a
  restrição por grupo é opt-in via a tela de Admin > Grupos. As
  permissões `artigos.ver`/`artigos.criar` ficaram órfãs (não gateiam
  mais nada) depois da tela de Artigos virar um link estático — podem
  ser removidas do catálogo numa limpeza futura.

- **Artigos → Mineralis / Master**: ✅ Pronta. A tela de Artigos deixou
  de ter CRUD/busca/cadastro em lote e virou uma página de apresentação
  com dois links: Mineralis (`https://mineralis.cetem.gov.br/buscar`),
  repositório institucional do CETEM, e Master (`https://master.cetem.gov.br/`),
  biblioteca digital com a produção técnico-científica publicada por
  editores externos ao Centro. O model/tabela `Artigo` e os PDFs já
  enviados foram mantidos no banco (não usados na UI), só por precaução
  — podem ser removidos numa limpeza futura se não fizerem mais falta.

## Pendências técnicas anotadas

- **Migração de dados do legado** (Ramais, Eventos, Informativos): existe
  o dump `intranet-legacy-slim/bck_intranet.sql` com os dados reais de
  produção (tabelas `telefones`, `events`, `article`). Planejar um comando
  Artisan de migração de dados (mapear `categories` → `sectors`, corrigir
  encoding latin1 → UTF-8, filtrar `article` por `id_index = 1` para
  Informativos). **Rodar perto do go-live**, não antes.

- **Integração com OnlyOffice** (Repositório de Arquivos): usar o
  OnlyOffice Document Server (container Docker `onlyoffice/documentserver`)
  para visualizar/editar documentos Office (Word/Excel/PowerPoint) direto
  no navegador. Integração via JSON de configuração assinado com JWT
  (`firebase/php-jwt`) + editor JS embutido na view + endpoint de callback
  no Laravel para salvar o arquivo editado. Não existe um SDK oficial
  Laravel — a integração é direta (controller + view + assinatura JWT),
  seguindo a documentação oficial do OnlyOffice Document Server.
FILE_EOF_1

cat > intranet-new/app/Http/Controllers/ArtigoController.php <<'FILE_EOF_2'
<?php

namespace App\Http\Controllers;

class ArtigoController extends Controller
{
    public function index()
    {
        return view('artigos.index');
    }
}
FILE_EOF_2

cat > intranet-new/app/Http/Controllers/DashboardController.php <<'FILE_EOF_3'
<?php

namespace App\Http\Controllers;

use App\Models\Arquivo;
use App\Models\Evento;
use App\Models\Informativo;

class DashboardController extends Controller
{
    public function index()
    {
        $informativos = Informativo::with('sector')->latest('published_at')->take(5)->get();

        $eventos = Evento::where('dt_start', '>=', now()->toDateString())
            ->orderBy('dt_start')
            ->take(5)
            ->get();

        $meusArquivos = Arquivo::whereHas('pasta', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->latest('updated_at')
            ->take(5)
            ->get();

        return view('dashboard', compact('informativos', 'eventos', 'meusArquivos'));
    }
}
FILE_EOF_3

cat > intranet-new/resources/views/artigos/index.blade.php <<'FILE_EOF_4'
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Artigos Científicos</h2>
    </x-slot>
    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white shadow rounded p-8 text-center">
                <h3 class="text-2xl font-bold text-blue-700 mb-4">Bem-vindo ao Mineralis</h3>
                <p class="text-gray-600 leading-relaxed mb-6">
                    O repositório institucional do CETEM coleta, preserva e distribui
                    material digital, mantendo os princípios da segurança da informação.
                </p>
                <a href="https://mineralis.cetem.gov.br/buscar" target="_blank" rel="noopener"
                   class="inline-block px-6 py-3 bg-blue-600 text-white font-semibold rounded hover:bg-blue-700">
                    Acessar o Mineralis
                </a>
            </div>

            <div class="bg-white shadow rounded p-8 text-center">
                <h3 class="text-2xl font-bold text-blue-700 mb-4">Bem-vindo ao Master</h3>
                <p class="text-gray-600 leading-relaxed mb-6">
                    A Biblioteca Digital Master disponibiliza a produção
                    técnico-científica do CETEM publicada por editores externos ao
                    Centro.
                </p>
                <a href="https://master.cetem.gov.br/" target="_blank" rel="noopener"
                   class="inline-block px-6 py-3 bg-blue-600 text-white font-semibold rounded hover:bg-blue-700">
                    Acessar o Master
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
FILE_EOF_4

cat > intranet-new/resources/views/dashboard.blade.php <<'FILE_EOF_5'
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Bem-vindo(a), {{ auth()->user()->name }}</h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <div class="bg-white shadow rounded p-4">
            <h3 class="font-semibold text-lg mb-3">Aplicações</h3>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('onlyoffice.aplicacoes') }}" class="px-4 py-2 bg-blue-600 text-white rounded">📄 Documento Word</a>
                <a href="{{ route('onlyoffice.aplicacoes') }}" class="px-4 py-2 bg-green-600 text-white rounded">📊 Planilha Excel</a>
                <a href="{{ route('onlyoffice.aplicacoes') }}" class="px-4 py-2 bg-orange-600 text-white rounded">📽️ Apresentação</a>
                <a href="{{ route('repositorio.index') }}" class="px-4 py-2 bg-gray-200 rounded">🗂️ Repositório de Arquivos</a>
                <a href="{{ route('telefones.index') }}" class="px-4 py-2 bg-gray-200 rounded">☎️ Ramais</a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <div class="bg-white shadow rounded p-4">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-semibold text-lg">📢 Mural de Avisos</h3>
                    <a href="{{ route('informativos.index') }}" class="text-sm text-blue-600">Ver todos</a>
                </div>
                <div class="space-y-3">
                    @forelse($informativos as $informativo)
                        <a href="{{ route('informativos.show', $informativo) }}" class="block border-b pb-2 last:border-0">
                            <p class="font-medium text-blue-700">{{ $informativo->title }}</p>
                            <p class="text-xs text-gray-500">{{ $informativo->sector->name ?? 'Geral' }} — {{ $informativo->published_at?->format('d/m/Y') }}</p>
                        </a>
                    @empty
                        <p class="text-sm text-gray-500">Nenhum informativo publicado ainda.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white shadow rounded p-4">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-semibold text-lg">📅 Agenda</h3>
                    <a href="{{ route('eventos.index') }}" class="text-sm text-blue-600">Ver todos</a>
                </div>
                <div class="space-y-3">
                    @forelse($eventos as $evento)
                        <div class="border-b pb-2 last:border-0">
                            <p class="font-medium">{{ $evento->title }}</p>
                            <p class="text-xs text-gray-500">{{ $evento->dt_start->format('d/m/Y') }} — {{ $evento->local }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Nenhum evento futuro.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white shadow rounded p-4">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-semibold text-lg">🔬 Artigos Científicos</h3>
                    <a href="{{ route('artigos.index') }}" class="text-sm text-blue-600">Ver mais</a>
                </div>
                <p class="text-sm text-gray-600 mb-3">
                    Os artigos científicos do CETEM estão disponíveis no Mineralis,
                    o repositório institucional.
                </p>
                <a href="https://mineralis.cetem.gov.br/buscar" target="_blank" rel="noopener" class="text-sm text-blue-600 font-medium">
                    Acessar o Mineralis &rarr;
                </a>
            </div>

            <div class="bg-white shadow rounded p-4">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-semibold text-lg">📁 Meus últimos arquivos</h3>
                    <a href="{{ route('repositorio.meus') }}" class="text-sm text-blue-600">Ver todos</a>
                </div>
                <div class="space-y-3">
                    @forelse($meusArquivos as $arquivo)
                        <div class="flex justify-between items-center border-b pb-2 last:border-0">
                            <span>{{ $arquivo->nome_original }}</span>
                            @if(in_array($arquivo->extensao, ['doc','docx','odt','xls','xlsx','ods','ppt','pptx','odp','pdf']))
                                <a href="{{ route('onlyoffice.editor', $arquivo) }}" class="text-sm text-green-700">abrir</a>
                            @else
                                <a href="{{ route('repositorio.download', $arquivo) }}" class="text-sm text-blue-700">baixar</a>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Nenhum arquivo por aqui ainda.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
FILE_EOF_5

cat > intranet-new/resources/views/layouts/navigation.blade.php <<'FILE_EOF_6'
<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-cetem-logo class="h-10 w-auto" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Tela Inicial') }}
                    </x-nav-link>
                    @if(auth()->user()->hasPermission('ramais.ver'))
                        <x-nav-link :href="route('telefones.index')" :active="request()->routeIs('telefones.*')">
                            {{ __('Ramais') }}
                        </x-nav-link>
                    @endif
                    @if(auth()->user()->hasPermission('informativos.ver'))
                        <x-nav-link :href="route('informativos.index')" :active="request()->routeIs('informativos.*')">
                            {{ __('Informativos') }}
                        </x-nav-link>
                    @endif
                    @if(auth()->user()->hasPermission('eventos.ver'))
                        <x-nav-link :href="route('eventos.index')" :active="request()->routeIs('eventos.*')">
                            {{ __('Agenda') }}
                        </x-nav-link>
                    @endif
                    <x-nav-link :href="route('artigos.index')" :active="request()->routeIs('artigos.*')">
                        {{ __('Artigos') }}
                    </x-nav-link>
                    @if(auth()->user()->hasPermission('repositorio.ver'))
                        <x-nav-link :href="route('repositorio.index')" :active="request()->routeIs('repositorio.*')">
                            {{ __('Repositório') }}
                        </x-nav-link>
                    @endif
                    <x-nav-link :href="route('onlyoffice.aplicacoes')" :active="request()->routeIs('onlyoffice.aplicacoes')">
                        {{ __('Aplicações') }}
                    </x-nav-link>
                    @if(auth()->user()->is_admin)
                        <x-nav-link :href="route('admin.index')" :active="request()->routeIs('admin.*')">
                            {{ __('Administração') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Tela Inicial') }}
            </x-responsive-nav-link>
            @if(auth()->user()->hasPermission('ramais.ver'))
                <x-responsive-nav-link :href="route('telefones.index')" :active="request()->routeIs('telefones.*')">
                    {{ __('Ramais') }}
                </x-responsive-nav-link>
            @endif
            @if(auth()->user()->hasPermission('informativos.ver'))
                <x-responsive-nav-link :href="route('informativos.index')" :active="request()->routeIs('informativos.*')">
                    {{ __('Informativos') }}
                </x-responsive-nav-link>
            @endif
            @if(auth()->user()->hasPermission('eventos.ver'))
                <x-responsive-nav-link :href="route('eventos.index')" :active="request()->routeIs('eventos.*')">
                    {{ __('Agenda') }}
                </x-responsive-nav-link>
            @endif
            <x-responsive-nav-link :href="route('artigos.index')" :active="request()->routeIs('artigos.*')">
                {{ __('Artigos') }}
            </x-responsive-nav-link>
            @if(auth()->user()->hasPermission('repositorio.ver'))
                <x-responsive-nav-link :href="route('repositorio.index')" :active="request()->routeIs('repositorio.*')">
                    {{ __('Repositório') }}
                </x-responsive-nav-link>
            @endif
            <x-responsive-nav-link :href="route('onlyoffice.aplicacoes')" :active="request()->routeIs('onlyoffice.aplicacoes')">
                {{ __('Aplicações') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
FILE_EOF_6

cat > intranet-new/routes/web.php <<'FILE_EOF_7'
<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\DashboardController;
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

use App\Http\Controllers\TelefoneController;

Route::middleware('auth')->group(function () {
    Route::resource('telefones', TelefoneController::class)
        ->middlewareFor(['index', 'show'], 'permission:ramais.ver')
        ->middlewareFor(['create', 'store', 'edit', 'update', 'destroy'], 'permission:ramais.criar');
    Route::get('telefones-lote', [TelefoneController::class, 'loteForm'])->name('telefones.lote.form')->middleware('permission:ramais.criar');
    Route::post('telefones-lote', [TelefoneController::class, 'loteImport'])->name('telefones.lote.import')->middleware('permission:ramais.criar');
    Route::get('telefones-lote/template', [TelefoneController::class, 'loteTemplate'])->name('telefones.lote.template')->middleware('permission:ramais.criar');
});

use App\Http\Controllers\InformativoController;

Route::middleware('auth')->group(function () {
    Route::resource('informativos', InformativoController::class)
        ->middlewareFor(['index', 'show'], 'permission:informativos.ver')
        ->middlewareFor(['create', 'store', 'edit', 'update', 'destroy'], 'permission:informativos.criar');
    Route::get('informativos/{informativo}/reenviar', [InformativoController::class, 'reenviarForm'])->name('informativos.reenviar.form')->middleware('permission:informativos.criar');
    Route::post('informativos/{informativo}/reenviar', [InformativoController::class, 'reenviar'])->name('informativos.reenviar')->middleware('permission:informativos.criar');
});

use App\Http\Controllers\EventoController;

Route::middleware('auth')->group(function () {
    Route::resource('eventos', EventoController::class)
        ->middlewareFor(['index', 'show'], 'permission:eventos.ver')
        ->middlewareFor(['create', 'store', 'edit', 'update', 'destroy'], 'permission:eventos.criar');
});

use App\Http\Controllers\EventoGravadoController;

Route::middleware('auth')->group(function () {
    Route::resource('eventos-gravados', EventoGravadoController::class)
        ->except(['index', 'show'])
        ->parameters(['eventos-gravados' => 'evento_gravado'])
        ->middleware('permission:eventos.criar');
    Route::get('eventos-gravados-lote', [EventoGravadoController::class, 'loteForm'])->name('eventos-gravados.lote.form')->middleware('permission:eventos.criar');
    Route::post('eventos-gravados-lote', [EventoGravadoController::class, 'loteImport'])->name('eventos-gravados.lote.import')->middleware('permission:eventos.criar');
    Route::get('eventos-gravados-lote/template', [EventoGravadoController::class, 'loteTemplate'])->name('eventos-gravados.lote.template')->middleware('permission:eventos.criar');
});

use App\Http\Controllers\ArtigoController;

Route::middleware('auth')->group(function () {
    Route::get('artigos', [ArtigoController::class, 'index'])->name('artigos.index');
});

use App\Http\Controllers\RepositorioController;

Route::middleware(['auth', 'permission:repositorio.ver'])->group(function () {
    Route::get('meus-arquivos', [RepositorioController::class, 'meusArquivos'])->name('repositorio.meus');
    Route::get('repositorio/{pasta?}', [RepositorioController::class, 'index'])->name('repositorio.index');
    Route::get('repositorio/arquivos/{arquivo}/download', [RepositorioController::class, 'download'])->name('repositorio.download');
});

Route::middleware(['auth', 'permission:repositorio.criar'])->group(function () {
    Route::post('repositorio/pastas', [RepositorioController::class, 'storePasta'])->name('repositorio.pastas.store');
    Route::get('repositorio/pastas/{pasta}/editar', [RepositorioController::class, 'editPasta'])->name('repositorio.pastas.editar');
    Route::put('repositorio/pastas/{pasta}', [RepositorioController::class, 'updatePasta'])->name('repositorio.pastas.update');
    Route::delete('repositorio/pastas/{pasta}', [RepositorioController::class, 'destroyPasta'])->name('repositorio.pastas.destroy');

    Route::post('repositorio/arquivos', [RepositorioController::class, 'storeArquivo'])->name('repositorio.arquivos.store');
    Route::get('repositorio/arquivos/{arquivo}/editar', [RepositorioController::class, 'editArquivo'])->name('repositorio.arquivos.editar');
    Route::put('repositorio/arquivos/{arquivo}', [RepositorioController::class, 'updateArquivo'])->name('repositorio.arquivos.update');
    Route::delete('repositorio/arquivos/{arquivo}', [RepositorioController::class, 'destroyArquivo'])->name('repositorio.arquivos.destroy');
});

use App\Http\Controllers\OnlyOfficeController;

Route::middleware('auth')->group(function () {
    Route::get('repositorio/arquivos/{arquivo}/editor', [OnlyOfficeController::class, 'editor'])->name('onlyoffice.editor');
});

Route::get('onlyoffice/documento/{arquivo}', [OnlyOfficeController::class, 'documento'])->name('onlyoffice.documento')->middleware('signed');
Route::post('onlyoffice/callback/{arquivo}', [OnlyOfficeController::class, 'callback'])->name('onlyoffice.callback')->middleware('signed');

Route::middleware('auth')->group(function () {
    Route::get('aplicacoes', [OnlyOfficeController::class, 'aplicacoes'])->name('onlyoffice.aplicacoes');
    Route::post('aplicacoes/criar', [OnlyOfficeController::class, 'criar'])->name('onlyoffice.criar');
});

use App\Http\Controllers\AdminController;

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');

    Route::get('setores', [AdminController::class, 'setores'])->name('setores');
    Route::post('setores', [AdminController::class, 'storeSetor'])->name('setores.store');
    Route::put('setores/{setor}', [AdminController::class, 'updateSetor'])->name('setores.update');
    Route::delete('setores/{setor}', [AdminController::class, 'destroySetor'])->name('setores.destroy');

    Route::get('grupos', [AdminController::class, 'grupos'])->name('grupos');
    Route::get('grupos/criar', [AdminController::class, 'criarGrupoForm'])->name('grupos.criar');
    Route::post('grupos', [AdminController::class, 'storeGrupo'])->name('grupos.store');
    Route::get('grupos/{grupo}/editar', [AdminController::class, 'editarGrupoForm'])->name('grupos.editar');
    Route::put('grupos/{grupo}', [AdminController::class, 'updateGrupo'])->name('grupos.update');
    Route::delete('grupos/{grupo}', [AdminController::class, 'destroyGrupo'])->name('grupos.destroy');

    Route::get('usuarios', [AdminController::class, 'usuarios'])->name('usuarios');
    Route::post('usuarios/{usuario}/toggle-admin', [AdminController::class, 'toggleAdmin'])->name('usuarios.toggle');
    Route::put('usuarios/{usuario}/setor', [AdminController::class, 'updateUsuarioSetor'])->name('usuarios.setor');
    Route::put('usuarios/{usuario}/grupo', [AdminController::class, 'updateUsuarioGrupo'])->name('usuarios.grupo');
    Route::delete('usuarios/{usuario}', [AdminController::class, 'destroyUsuario'])->name('usuarios.destroy');

    Route::get('usuarios-lote', [AdminController::class, 'usuariosLoteForm'])->name('usuarios.lote.form');
    Route::post('usuarios-lote', [AdminController::class, 'usuariosLoteImport'])->name('usuarios.lote.import');
    Route::get('usuarios-lote/template', [AdminController::class, 'usuariosLoteTemplate'])->name('usuarios.lote.template');

    Route::get('usuarios-grupo-lote', [AdminController::class, 'usuariosGrupoLoteForm'])->name('usuarios.grupo-lote.form');
    Route::post('usuarios-grupo-lote', [AdminController::class, 'usuariosGrupoLoteImport'])->name('usuarios.grupo-lote.import');
    Route::get('usuarios-grupo-lote/template', [AdminController::class, 'usuariosGrupoLoteTemplate'])->name('usuarios.grupo-lote.template');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('usuarios/criar', [AdminController::class, 'criarUsuarioForm'])->name('usuarios.criar');
    Route::post('usuarios', [AdminController::class, 'storeUsuario'])->name('usuarios.store');
});
FILE_EOF_7

cat > intranet-new/tests/Feature/GroupViewOnlyAllScreensTest.php <<'FILE_EOF_8'
<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupViewOnlyAllScreensTest extends TestCase
{
    use RefreshDatabase;

    private function criarUsuarioLeitor(array $verKeys): User
    {
        $group = Group::create(['name' => 'Leitor-' . implode('-', $verKeys)]);
        $group->permissions()->sync(Permission::whereIn('key', $verKeys)->pluck('id'));

        return User::factory()->create(['group_id' => $group->id]);
    }

    public function test_leitor_de_ramais_acessa_index(): void
    {
        $user = $this->criarUsuarioLeitor(['ramais.ver']);
        $this->actingAs($user)->get(route('telefones.index'))->assertOk();
    }

    public function test_leitor_de_informativos_acessa_index(): void
    {
        $user = $this->criarUsuarioLeitor(['informativos.ver']);
        $this->actingAs($user)->get(route('informativos.index'))->assertOk();
    }

    public function test_leitor_de_eventos_acessa_index(): void
    {
        $user = $this->criarUsuarioLeitor(['eventos.ver']);
        $this->actingAs($user)->get(route('eventos.index'))->assertOk();
    }

    public function test_leitor_de_repositorio_acessa_index(): void
    {
        $user = $this->criarUsuarioLeitor(['repositorio.ver']);
        $this->actingAs($user)->get(route('repositorio.index'))->assertOk();
    }

    public function test_leitor_de_tudo_acessa_todas_as_telas(): void
    {
        $user = $this->criarUsuarioLeitor([
            'ramais.ver', 'informativos.ver', 'eventos.ver', 'repositorio.ver',
        ]);

        $this->actingAs($user)->get(route('telefones.index'))->assertOk();
        $this->actingAs($user)->get(route('informativos.index'))->assertOk();
        $this->actingAs($user)->get(route('eventos.index'))->assertOk();
        $this->actingAs($user)->get(route('repositorio.index'))->assertOk();

        // E continua sem poder criar em nenhuma delas.
        $this->actingAs($user)->get(route('telefones.create'))->assertForbidden();
        $this->actingAs($user)->get(route('informativos.create'))->assertForbidden();
        $this->actingAs($user)->get(route('eventos.create'))->assertForbidden();
    }

    public function test_leitor_nao_ve_botoes_de_criar_editar_remover(): void
    {
        $user = $this->criarUsuarioLeitor([
            'ramais.ver', 'informativos.ver', 'eventos.ver', 'repositorio.ver',
        ]);

        \App\Models\Telefone::create([
            'nome' => 'Fulano', 'telefone' => '1234',
            'sector_id' => \App\Models\Sector::create(['name' => 'TI'])->id,
        ]);
        \App\Models\Informativo::create(['title' => 'Aviso', 'content' => 'x', 'published_at' => now()]);

        $this->actingAs($user)->get(route('telefones.index'))
            ->assertOk()->assertDontSee('Novo ramal')->assertDontSee('Cadastro em lote')->assertDontSee('Remover');

        $this->actingAs($user)->get(route('informativos.index'))
            ->assertOk()->assertDontSee('Novo informativo')->assertDontSee('Editar')->assertDontSee('Remover');

        $this->actingAs($user)->get(route('eventos.index'))
            ->assertOk()->assertDontSee('Novo evento')->assertDontSee('Novo evento gravado');

        $this->actingAs($user)->get(route('repositorio.index'))
            ->assertOk()->assertDontSee('Nova pasta')->assertDontSee('Enviar arquivo');
    }

    public function test_artigos_e_acessivel_a_qualquer_usuario_autenticado_e_mostra_links_mineralis_e_master(): void
    {
        $user = $this->criarUsuarioLeitor([]);

        $this->actingAs($user)->get(route('artigos.index'))
            ->assertOk()
            ->assertSee('Mineralis')
            ->assertSee('https://mineralis.cetem.gov.br/buscar', false)
            ->assertSee('Master')
            ->assertSee('https://master.cetem.gov.br/', false);
    }

    public function test_grupo_leitor_de_informativos_nao_ve_botao_de_reenviar(): void
    {
        $user = $this->criarUsuarioLeitor(['informativos.ver']);
        $informativo = \App\Models\Informativo::create(['title' => 'Aviso', 'content' => 'x', 'published_at' => now()]);

        $this->actingAs($user)->get(route('informativos.show', $informativo))
            ->assertOk()->assertDontSee('Reenviar e-mails');
    }
}
FILE_EOF_8

git add -A
git status --short
git commit -m "Simplifica aba Artigos: apresentação e links para Mineralis e Master

Remove todo o CRUD, busca e cadastro em lote de Artigos Científicos.
A tela vira uma página de apresentação com dois links: Mineralis
(https://mineralis.cetem.gov.br/buscar), o repositório institucional
do CETEM, e Master (https://master.cetem.gov.br/), a biblioteca
digital com a produção técnico-científica publicada por editores
externos ao Centro. Acessível a qualquer usuário autenticado, sem gate
de permissão, já que não há mais dados sensíveis na tela.

O widget \"Últimos Artigos\" do dashboard também foi ajustado para
apontar para o Mineralis. O model/tabela Artigo e os PDFs já enviados
foram mantidos no banco por precaução, apenas sem uso na UI.

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>
Claude-Session: https://claude.ai/code/session_013hcEEXZu91mYj9ytM7sc2R"
git log --oneline -3
