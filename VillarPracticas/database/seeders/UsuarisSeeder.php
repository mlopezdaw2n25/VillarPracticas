<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\usuaris;

class UsuarisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        for($i = 0; $i < 5; $i++){
            $u = new usuaris();
            $u->nom = "mario".$i;
            $u->email = "mlopez".$i;
            $u->contrasenya = "contrasenya".$i;
            $u->correo_notificaciones = "vacio".$i;
            $u->rol = "profesor";
            $u->activo = "si".$i;
            $u->save();
        }
    }
}
