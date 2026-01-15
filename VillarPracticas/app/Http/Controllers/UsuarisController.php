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
    $usuari = usuaris::where('email', $req->email)
                     ->where('contrasenya', $req->password)
                     ->first();

    if ($usuari) {

        session([
            'usuari_id' => $usuari->id,
            'rol' => $usuari->rol
        ]);

        return redirect("Profesors/profesor/{$usuari->id}");
    }

    return back()->with('error', 'Usuari o contrasenya incorrectes!');
}

public function VistaProfes(){
    return view('Profesors.profesor');
}

public function VistaListado(){
    return view('Profesors.listado');
}

}
