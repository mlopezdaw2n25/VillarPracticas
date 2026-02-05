<?php

use App\Http\Controllers\UsuarisController;
use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\ListadoDataController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\MyReservationsController;

use App\Http\Middleware\CheckLogin;
use App\Http\Middleware\CheckAdmin;
use App\Http\Middleware\CheckProfe;

use Illuminate\Support\Facades\Route;

// LOGIN
Route::get('/', [UsuarisController::class, 'index']);
Route::post('/', [UsuarisController::class, 'login']);

// LOGOUT
Route::get('/logout', [UsuarisController::class, 'logout'])->name('logout');

// SOLO USUARIOS LOGUEADOS
Route::middleware([CheckLogin::class])->group(function () {

    // SOLO PROFESOR
    Route::middleware([CheckProfe::class])->group(function () {

        // VISTAS
        Route::get('Profesors/profesor/{id}', [UsuarisController::class, 'VistaProfes']);
        Route::get('Profesors/listado/{id}', [UsuarisController::class, 'VistaListado']);
        Route::get('Profesors/misreservas/{id}', [UsuarisController::class, 'VistaMisReservas']);

        // ENDPOINTS (CON SESION + CSRF)
        Route::get('/availability', [AvailabilityController::class, 'index']);
        Route::get('/listado-data', [ListadoDataController::class, 'index']);
        Route::post('/reservations', [ReservationController::class, 'store']);

        // Mis reservas (API)
        Route::get('/my-reservations', [MyReservationsController::class, 'index']);
        Route::put('/reservations/{id}', [MyReservationsController::class, 'update']);
        Route::delete('/reservations/{id}', [MyReservationsController::class, 'destroy']);
        });

    // SOLO ADMIN
    Route::middleware([CheckAdmin::class])->group(function () {
        Route::get('Profesors/admin/{id}', [UsuarisController::class, 'VistaAdmin']);
    });
});

Route::delete('/reservations/{id}', [ReservationController::class, 'destroy']);

Route::get('/time-test', function () {
    return now()->toDateTimeString();
});
