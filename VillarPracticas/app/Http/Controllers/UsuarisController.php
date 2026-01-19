<?php

namespace App\Http\Controllers;

use App\Models\usuaris;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class UsuarisController extends Controller
{
    public function index(){
        return view("home");
    }

public function login(Request $req)
{
    $email = $req->input('email');
    $password = $req->input('password');

    $usuari = usuaris::where('email', $email)->first();

    if ($usuari && $usuari->contrasenya == $password) {

        session([
            'usuari_id' => $usuari->id,
            'rol' => $usuari->rol
        ]);

        if ($usuari->rol === 'admin') {
            return redirect("Profesors/admin/" . $usuari->id);
        }

        if ($usuari->rol === 'profe') {
            return redirect("Profesors/profesor/" . $usuari->id);
        }

        return back()->with('error', 'Rol no reconegut');
    }

    return back()->with('error', 'Usuari o contrasenya incorrectes!');
}

public function VistaProfes($id)
{
    if (session('rol') !== 'profe') {
        return redirect('/')->with('error', 'No tens permisos');
    }
    $profesor = usuaris::find($id);
    return view('Profesors/profesor', ['id' => $id, 'profesor' => $profesor]);
}

public function VistaListado($id){
    return view('Profesors.listado', ['id' => $id]);
}
public function VistaAdmin($id)
{
    if (session('rol') !== 'admin') {
        return redirect('/')->with('error', 'No tens permisos');
    }

    return view('Profesors/admin', ['id' => $id]);
}
public function logout()
{
    session()->flush(); // borra toda la sesión
    return redirect('/')->with('success', 'Has tancat sessió correctament');
}

}

