<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('Profesors/profesor', function () {
    return view('profesor');
});

Route::get('Profesors/listado', function () {
    return view('listado');
});
