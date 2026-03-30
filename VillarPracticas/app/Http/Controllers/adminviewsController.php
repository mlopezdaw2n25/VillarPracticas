<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class adminviewsController extends Controller
{
    public function averiasmaterial() {
        return view('/Admin/averias/material');
    }

    public function estadisticasespacio() {
        return view('/Admin/estadisticas/espacios');
    }

    public function estadisticasmaterial() {
        return view('/Admin/estadisticas/material');
    }

    public function gestionespacios() {
        return view('/Admin/gestion/espacios');
    }

    public function gestionmaterial() {
        return view('/Admin/gestion/materiales');
    }

    public function gestionreservas() {
        return view('/Admin/gestion/reservas');
    }

    public function gestionusuarios() {
        return view('/Admin/gestion/usuarios');
    }
}
