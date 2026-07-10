<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

use App\Http\Controllers\TelefoneController;

Route::middleware('auth')->group(function () {
    Route::resource('telefones', TelefoneController::class);
});

use App\Http\Controllers\InformativoController;

Route::middleware('auth')->group(function () {
    Route::resource('informativos', InformativoController::class);
});

use App\Http\Controllers\EventoController;

Route::middleware('auth')->group(function () {
    Route::resource('eventos', EventoController::class);
});

use App\Http\Controllers\ArtigoController;

Route::middleware('auth')->group(function () {
    Route::resource('artigos', ArtigoController::class);
});

Route::middleware('auth')->group(function () {
    Route::get('artigos-lote', [ArtigoController::class, 'loteForm'])->name('artigos.lote.form');
    Route::post('artigos-lote', [ArtigoController::class, 'loteImport'])->name('artigos.lote.import');
    Route::get('artigos-lote/template', [ArtigoController::class, 'loteTemplate'])->name('artigos.lote.template');
});

use App\Http\Controllers\RepositorioController;

Route::middleware('auth')->group(function () {
    Route::get('meus-arquivos', [RepositorioController::class, 'meusArquivos'])->name('repositorio.meus');
    Route::get('repositorio/{pasta?}', [RepositorioController::class, 'index'])->name('repositorio.index');
    Route::post('repositorio/pastas', [RepositorioController::class, 'storePasta'])->name('repositorio.pastas.store');
    Route::get('repositorio/pastas/{pasta}/editar', [RepositorioController::class, 'editPasta'])->name('repositorio.pastas.editar');
    Route::put('repositorio/pastas/{pasta}', [RepositorioController::class, 'updatePasta'])->name('repositorio.pastas.update');
    Route::delete('repositorio/pastas/{pasta}', [RepositorioController::class, 'destroyPasta'])->name('repositorio.pastas.destroy');

    Route::post('repositorio/arquivos', [RepositorioController::class, 'storeArquivo'])->name('repositorio.arquivos.store');
    Route::get('repositorio/arquivos/{arquivo}/download', [RepositorioController::class, 'download'])->name('repositorio.download');
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
