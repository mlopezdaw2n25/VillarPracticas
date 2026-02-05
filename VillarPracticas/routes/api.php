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
| En este proyecto usamos sesión (no Sanctum / Auth Laravel),
| por eso necesitamos middleware "web" para cookies + session().
| También implica CSRF en POST/PUT/DELETE.
*/

Route::middleware(['web'])->group(function () {

    // Disponibilidad de slots por fecha
    Route::get('/availability', [AvailabilityController::class, 'index']);

    // Datos para la vista listado (recursos por slot en una fecha)
    Route::get('/listado-data', [ListadoDataController::class, 'index']);

    // Crear reservas (multi-slot + items)
    Route::post('/reservations', [ReservationController::class, 'store']);

    // Mis reservas (listado)
    Route::get('/my-reservations', [MyReservationsController::class, 'index']);

    // Modificar reserva (fecha/slot)
    Route::put('/reservations/{id}', [MyReservationsController::class, 'update']);

    // Anular reserva
    Route::delete('/reservations/{id}', [MyReservationsController::class, 'destroy']);

    Route::get('/all-reservations', [AllReservationsController::class, 'index']);
});


