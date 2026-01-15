<?php
use App\Http\Controllers\UsuarisController;
use App\Http\Middleware\CheckLogin;
use Illuminate\Support\Facades\Route;

Route::get('/', [UsuarisController::class, 'index']);
Route::post('/', [UsuarisController::class, 'login']);

Route::middleware(CheckLogin::class)->group(function () {
    Route::get('Profesors/profesor/{id}', [UsuarisController::class, 'VistaProfes']);
    Route::get('Profesors/listado/{id}', [UsuarisController::class, 'VistaListado']);
});
