<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\DashboardController;
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified', 'registrar.acesso:dashboard'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

use App\Http\Controllers\TelefoneController;

Route::middleware('auth')->group(function () {
    Route::resource('telefones', TelefoneController::class)
        ->middlewareFor(['index'], ['permission:ramais.ver', 'registrar.acesso:ramais'])
        ->middlewareFor(['show'], 'permission:ramais.ver')
        ->middlewareFor(['create', 'store', 'edit', 'update', 'destroy'], 'permission:ramais.criar');
    Route::get('telefones-lote', [TelefoneController::class, 'loteForm'])->name('telefones.lote.form')->middleware('permission:ramais.criar');
    Route::post('telefones-lote', [TelefoneController::class, 'loteImport'])->name('telefones.lote.import')->middleware('permission:ramais.criar');
    Route::get('telefones-lote/template', [TelefoneController::class, 'loteTemplate'])->name('telefones.lote.template')->middleware('permission:ramais.criar');
});

use App\Http\Controllers\InformativoController;

Route::middleware('auth')->group(function () {
    Route::resource('informativos', InformativoController::class)
        ->middlewareFor(['index'], ['permission:informativos.ver', 'registrar.acesso:informativos'])
        ->middlewareFor(['show'], 'permission:informativos.ver')
        ->middlewareFor(['create', 'store', 'edit', 'update', 'destroy'], 'permission:informativos.criar');
    Route::get('informativos/{informativo}/reenviar', [InformativoController::class, 'reenviarForm'])->name('informativos.reenviar.form')->middleware('permission:informativos.criar');
    Route::post('informativos/{informativo}/reenviar', [InformativoController::class, 'reenviar'])->name('informativos.reenviar')->middleware('permission:informativos.criar');
    Route::get('informativos-lote', [InformativoController::class, 'loteForm'])->name('informativos.lote.form')->middleware('permission:informativos.criar');
    Route::post('informativos-lote', [InformativoController::class, 'loteImport'])->name('informativos.lote.import')->middleware('permission:informativos.criar');
    Route::get('informativos-lote/template', [InformativoController::class, 'loteTemplate'])->name('informativos.lote.template')->middleware('permission:informativos.criar');
});

use App\Http\Controllers\EventoController;

Route::middleware('auth')->group(function () {
    Route::resource('eventos', EventoController::class)
        ->middlewareFor(['index'], ['permission:eventos.ver', 'registrar.acesso:eventos'])
        ->middlewareFor(['show'], 'permission:eventos.ver')
        ->middlewareFor(['create', 'store', 'edit', 'update', 'destroy'], 'permission:eventos.criar');
    Route::get('eventos-lote', [EventoController::class, 'loteForm'])->name('eventos.lote.form')->middleware('permission:eventos.criar');
    Route::post('eventos-lote', [EventoController::class, 'loteImport'])->name('eventos.lote.import')->middleware('permission:eventos.criar');
    Route::get('eventos-lote/template', [EventoController::class, 'loteTemplate'])->name('eventos.lote.template')->middleware('permission:eventos.criar');
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

use App\Http\Controllers\TutorialController;

Route::middleware('auth')->group(function () {
    Route::resource('tutoriais', TutorialController::class)
        ->parameters(['tutoriais' => 'tutorial'])
        ->middlewareFor(['index'], ['permission:tutoriais.ver', 'registrar.acesso:tutoriais'])
        ->middlewareFor(['show'], 'permission:tutoriais.ver')
        ->middlewareFor(['create', 'store', 'edit', 'update', 'destroy'], 'permission:tutoriais.criar');
    Route::get('tutoriais-lote', [TutorialController::class, 'loteForm'])->name('tutoriais.lote.form')->middleware('permission:tutoriais.criar');
    Route::post('tutoriais-lote', [TutorialController::class, 'loteImport'])->name('tutoriais.lote.import')->middleware('permission:tutoriais.criar');
    Route::get('tutoriais-lote/template', [TutorialController::class, 'loteTemplate'])->name('tutoriais.lote.template')->middleware('permission:tutoriais.criar');
});

use App\Http\Controllers\DestaqueController;

Route::middleware('auth')->group(function () {
    Route::resource('destaques', DestaqueController::class)
        ->middlewareFor(['index'], ['permission:destaques.ver', 'registrar.acesso:destaques'])
        ->middlewareFor(['show'], 'permission:destaques.ver')
        ->middlewareFor(['create', 'store', 'edit', 'update', 'destroy'], 'permission:destaques.criar');
});

use App\Http\Controllers\BuscaController;

Route::get('busca', [BuscaController::class, 'index'])->name('busca.index')->middleware('auth');

use App\Http\Controllers\ArtigoController;

Route::middleware('auth')->group(function () {
    Route::get('artigos', [ArtigoController::class, 'index'])->name('artigos.index');
});

use App\Http\Controllers\RepositorioController;

Route::middleware(['auth', 'permission:repositorio.ver'])->group(function () {
    Route::get('meus-arquivos', [RepositorioController::class, 'meusArquivos'])->name('repositorio.meus');
    Route::get('repositorio/{pasta?}', [RepositorioController::class, 'index'])->name('repositorio.index')->middleware('registrar.acesso:repositorio');
    Route::get('repositorio/arquivos/{arquivo}/download', [RepositorioController::class, 'download'])->name('repositorio.download');
    Route::get('repositorio/arquivos/{arquivo}/ocr-status', [RepositorioController::class, 'ocrStatus'])->name('repositorio.arquivos.ocr-status');
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

    Route::get('repositorio-arquivos-lote', [RepositorioController::class, 'loteArquivosForm'])->name('repositorio.arquivos.lote.form');
    Route::post('repositorio-arquivos-lote', [RepositorioController::class, 'loteArquivosImport'])->name('repositorio.arquivos.lote.import');
    Route::get('repositorio-arquivos-lote/template', [RepositorioController::class, 'loteArquivosTemplate'])->name('repositorio.arquivos.lote.template');
});

use App\Http\Controllers\OnlyOfficeController;

Route::middleware('auth')->group(function () {
    Route::get('repositorio/arquivos/{arquivo}/editor', [OnlyOfficeController::class, 'editor'])->name('onlyoffice.editor');
});

Route::get('onlyoffice/documento/{arquivo}', [OnlyOfficeController::class, 'documento'])->name('onlyoffice.documento')->middleware('signed');
Route::post('onlyoffice/callback/{arquivo}', [OnlyOfficeController::class, 'callback'])->name('onlyoffice.callback')->middleware('signed');

use App\Http\Controllers\PaperlessWebhookController;

Route::post('webhooks/paperless', [PaperlessWebhookController::class, 'handle'])->name('webhooks.paperless');

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

    Route::get('armazenamento', [AdminController::class, 'armazenamento'])->name('armazenamento');
    Route::get('engajamento', [AdminController::class, 'engajamento'])->name('engajamento');
    Route::get('conteudo', [AdminController::class, 'conteudo'])->name('conteudo');

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
