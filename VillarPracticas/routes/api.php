<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\ListadoDataController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\MyReservationsController;
use App\Http\Controllers\Api\AllReservationsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['web'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DISPONIBILIDAD
    |--------------------------------------------------------------------------
    */

    // Disponibilidad de slots por fecha
    Route::get('/availability', [AvailabilityController::class, 'index']);

    // Datos para la vista listado (recursos por slot en una fecha)
    Route::get('/listado-data', [ListadoDataController::class, 'index']);


    /*
    |--------------------------------------------------------------------------
    | CREAR RESERVA
    |--------------------------------------------------------------------------
    */

    // Crear reservas (multi-slot + items)
    Route::post('/reservations', [ReservationController::class, 'store']);


    /*
    |--------------------------------------------------------------------------
    | MIS RESERVAS
    |--------------------------------------------------------------------------
    */

    // Listado de mis reservas
    Route::get('/my-reservations', [MyReservationsController::class, 'index']);

    // Modificar fecha / slot
    Route::put('/reservations/{id}', [MyReservationsController::class, 'update']);

    // Anular reserva
    Route::delete('/reservations/{id}', [MyReservationsController::class, 'destroy']);


    /*
    |--------------------------------------------------------------------------
    | TODAS LAS RESERVAS
    |--------------------------------------------------------------------------
    */

    Route::get('/all-reservations', [AllReservationsController::class, 'index']);


    /*
    |--------------------------------------------------------------------------
    | EDICIÓN DE MATERIALES
    |--------------------------------------------------------------------------
    */

    // Obtener datos completos de una reserva (para el modal editar)
    Route::get('/reservations/{id}', [ReservationController::class, 'show']);

    // Actualizar materiales de una reserva
    Route::put('/reservations/{id}/items', [ReservationController::class, 'updateItems']);
});
