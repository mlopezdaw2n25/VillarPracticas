<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\usuaris;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        for($i = 0; $i < 5; $i++){
            $u = new usuaris();
            $u->nom = "dani".$i;
            $u->email = "admin".$i."@gmail.com";
            $u->contrasenya = "contrasenya".$i;
            $u->correo_notificaciones = "vacio".$i;
            $u->rol = "admin";
            $u->activo = "si";
            $u->save();
        }
    }
}
