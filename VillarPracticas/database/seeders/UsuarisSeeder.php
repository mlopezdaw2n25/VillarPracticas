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
        for($i = 0; $i < 1; $i++){
            $u = new usuaris();
            $u->nom = "correo".$i;
            $u->email = "dbernaus.daw2n25@lamerce.com";
            $u->contrasenya = "1";
            $u->correo_notificaciones = "dbernaus.daw2n25@lamerce.com";
            $u->rol = "profe";
            $u->activo = "si";
            $u->save();
        }
    }
}
