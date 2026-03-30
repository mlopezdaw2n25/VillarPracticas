<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\usuaris;
use App\Models\Resource;
use App\Models\Reservation;

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
        $materiales = Resource::where('type', 2)->get();
        return view('/Admin/gestion/espacios', ['espacios' => $materiales]);
    }

    public function gestionmaterial() {
        $materiales = Resource::where('type', 1)->get();
        return view('/Admin/gestion/materiales', ['materiales' => $materiales]);
    }

    public function gestionreservas() {
        //no esta acabado al 100%
        $reservas = Reservation::with('usuari')->get();
        return view('/Admin/gestion/reservas', compact('reservas')); 
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

    public function crearMaterial(Request $req) {
        $material = new Resource();
        $material->name = $req->input('nom');
        $material->total_units = $req->input('cantidad');
        $material->type = 1;
        $material->save();
        $materiales = Resource::where('type', 1)->get();
        return view('/Admin/gestion/materiales', ['materiales' => $materiales]);
    }

    public function crearEspacio(Request $req){
        $material = new Resource();
        $material->name = $req->input('nom');
        $material->total_units = 1;
        $material->type = 2;
        $material->save();
        $materiales = Resource::where('type', 2)->get();
        return view('/Admin/gestion/espacios', ['espacios' => $materiales]);
    }
}
