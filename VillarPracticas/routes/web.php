<?php

use App\Http\Controllers\UsuarisController;
use App\Http\Middleware\CheckLogin;
use App\Http\Middleware\CheckAdmin;
use App\Http\Middleware\CheckProfe;
use Illuminate\Support\Facades\Route;

Route::get('/', [UsuarisController::class, 'index']);
Route::post('/', [UsuarisController::class, 'login']);

// SOLO para usuarios logueados (admin o profe)
Route::middleware([CheckLogin::class])->group(function () {

    // SOLO PROFESOR
    Route::middleware([CheckProfe::class])->group(function () {
        Route::get('Profesors/profesor/{id}', [UsuarisController::class, 'VistaProfes']);
        Route::get('Profesors/listado/{id}', [UsuarisController::class, 'VistaListado']);
    });

    // SOLO ADMIN
    Route::middleware([CheckAdmin::class])->group(function () {
        Route::get('Profesors/admin/{id}', [UsuarisController::class, 'VistaAdmin']);
    });

});

Route::get('/logout', [UsuarisController::class, 'logout'])->name('logout');
Route::get('Profesors/profesor/{id}', [UsuarisController::class, 'VistaProfes']);