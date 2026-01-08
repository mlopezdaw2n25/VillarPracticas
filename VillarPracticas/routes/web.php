<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('Profesors/profesor', function () {
    return view('Profesors.profesor');
});

Route::get('Profesors/listado', function () {
    return view('Profesors.listado');
});
