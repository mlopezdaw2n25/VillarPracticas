<?php

use App\Http\Controllers\UsuarisController;
use App\Http\Controllers\ListadoController;
use App\Http\Controllers\adminviewsController;
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

    Route::middleware([CheckProfe::class])->group(function () {

        Route::get('/listado', [ListadoController::class, 'index']);

        // VISTAS
        Route::get('Profesors/profesor/{id}', [UsuarisController::class, 'VistaProfes']);
        Route::get('Profesors/listado/{id}', [UsuarisController::class, 'VistaListado']);
        Route::get('Profesors/misreservas/{id}', [UsuarisController::class, 'VistaMisReservas']);

        // ENDPOINTS
        Route::get('/availability', [AvailabilityController::class, 'index']);
        Route::get('/listado-data', [ListadoDataController::class, 'index']);
        Route::post('/reservations', [ReservationController::class, 'store']);

        // MIS RESERVAS API
        Route::get('/my-reservations', [MyReservationsController::class, 'index']);
        Route::put('/reservations/{id}', [MyReservationsController::class, 'update']);

        // 👇 ESTAS DOS VAN AQUÍ
        Route::delete('/reservations/{id}', [ReservationController::class, 'destroy']);
        Route::post('/reservations/{id}/confirm-return', [ReservationController::class, 'confirmReturn']);

    });

    Route::middleware([CheckAdmin::class])->group(function () {
        Route::controller(adminviewsController::class)->group(function (){
            //get
            Route::get('/Admin/averias/material', 'averiasmaterial');
            Route::get('/Admin/estadisticas/espacios', 'estadisticasespacio');
            Route::get('/Admin/estadisticas/material', 'estadisticasmaterial');
            Route::get('/Admin/gestion/espacios', 'gestionespacios');
            Route::get('/Admin/gestion/materiales', 'gestionmaterial');
            Route::get('/Admin/gestion/reservas', 'gestionreservas');
            Route::get('/Admin/gestion/usuarios', 'gestionusuarios');

            //post
            Route::post('/Admin/gestion/usuarios', 'crearUsuario');
            Route::post('/Admin/gestion/materiales', 'crearMaterial');
            Route::post('/Admin/gestion/espacios', 'crearespacio');
        });
    });

    Route::middleware([CheckAdmin::class])->group(function () {
        Route::get('/Admin/gestion/usuarios', [UsuarisController::class, 'VistaAdmin']);
    });

});

Route::get('/time-test', function () {
    return now()->toDateTimeString();
});
