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
