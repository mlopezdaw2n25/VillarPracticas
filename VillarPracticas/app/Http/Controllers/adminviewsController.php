<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\usuaris;

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
        $usuaris = usuaris::all();
        return view('/Admin/gestion/usuarios', ['usuaris' => $usuaris]);
    }

    public function crearUsuario(Request $req) {
        $usuari = new usuaris();
        $usuari->nom = $req->input('nom');
        $usuari->email = $req->input('email');
        $usuari->contrasenya = $req->input('contrasenya');
        $usuari->rol = $req->input('rol');
        $usuari->correo_notificaciones = $req->input('email');
        $usuari->activo = 1;
        $usuari->save();
        $usuaris = usuaris::all();
        return view('/Admin/gestion/usuarios', ['usuaris' => $usuaris]);
    }
}
