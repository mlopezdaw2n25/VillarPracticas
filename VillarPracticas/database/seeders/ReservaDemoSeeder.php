<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReservaDemoSeeder extends Seeder
{
    public function run(): void
    {
        // ----------------------------
        // 1) TIME SLOTS (09:00 - 20:00)
        // ----------------------------
        if (DB::table('time_slots')->count() === 0) {
            for ($h = 9; $h <= 19; $h++) {
                DB::table('time_slots')->insert([
                    'start_time' => sprintf('%02d:00:00', $h),
                    'end_time' => sprintf('%02d:00:00', $h + 1),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // ----------------------------
        // 2) RESOURCES (material/space)
        // ----------------------------
        if (DB::table('resources')->count() === 0) {
            $resources = [
                // spaces (stock 1)
                ['name' => 'Plató 1', 'type' => 'space', 'total_units' => 1],
                ['name' => 'Plató 2', 'type' => 'space', 'total_units' => 1],
                ['name' => 'Plató Superior', 'type' => 'space', 'total_units' => 1],

                // materials
                ['name' => 'Cámara Canon', 'type' => 'material', 'total_units' => 3],
                ['name' => 'Trípode', 'type' => 'material', 'total_units' => 5],
                ['name' => 'Micrófono', 'type' => 'material', 'total_units' => 6],
                ['name' => 'Kit iluminación', 'type' => 'material', 'total_units' => 2],
            ];

            foreach ($resources as $r) {
                DB::table('resources')->insert([
                    ...$r,
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // ----------------------------
        // 3) SLOT_RESOURCES
        // (asignar recursos a cada slot)
        // ----------------------------
        $slots = DB::table('time_slots')->get();
        $resources = DB::table('resources')->get();

        foreach ($slots as $slot) {
            foreach ($resources as $res) {
                DB::table('slot_resources')->updateOrInsert(
                    [
                        'time_slot_id' => $slot->id,
                        'resource_id' => $res->id,
                    ],
                    [
                        'available_units' => $res->total_units, // stock por franja
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        // ----------------------------
        // 4) RESERVAS DE EJEMPLO (para probar disabled)
        // ----------------------------
        // Creamos reservas de consumo para que algunas franjas queden medio o totalmente agotadas
        $today = now()->format('Y-m-d');

        $userId = DB::table('usuaris')->value('id') ?? null;

        // si no hay usuarios, crea uno rápido
        if (!$userId) {
            $userId = DB::table('usuaris')->insertGetId([
                'nom' => 'Profe Demo',
                'email' => 'profe@demo.com',
                'password' => bcrypt('1234'),
                'rol' => 'profe',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Reservas en slot 1, 2 y 3 para simular consumo
        $slotIds = DB::table('time_slots')->limit(3)->pluck('id');

        foreach ($slotIds as $slotId) {
            $reservationId = DB::table('reservations')->insertGetId([
                'user_id' => $userId,
                'date' => $today,
                'time_slot_id' => $slotId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // consume recursos random
            $materials = DB::table('resources')->where('type', 'material')->get();
            foreach ($materials->take(2) as $mat) {
                DB::table('reservation_items')->insert([
                    'reservation_id' => $reservationId,
                    'resource_id' => $mat->id,
                    'quantity' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // consume un espacio (dejándolo ocupado)
            $space = DB::table('resources')->where('type', 'space')->inRandomOrder()->first();
            if ($space) {
                DB::table('reservation_items')->insert([
                    'reservation_id' => $reservationId,
                    'resource_id' => $space->id,
                    'quantity' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
