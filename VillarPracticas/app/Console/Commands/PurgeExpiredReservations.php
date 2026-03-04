<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurgeExpiredReservations extends Command
{
    protected $signature = 'reservations:purge-expired';
    protected $description = 'Marca reservas caducadas como pendientes de devolución (no elimina)';

    public function handle()
    {
        $now = now()->format('Y-m-d H:i:s');

        // Selecciona reservas ACTIVAS cuyo final ya pasó:
        // end_datetime = CONCAT(date, ' ', time_slots.end_time)
        $expired = DB::table('reservations as r')
            ->join('time_slots as ts', 'ts.id', '=', 'r.time_slot_id')
            ->where('r.status', 'activa')
            ->whereRaw("STR_TO_DATE(CONCAT(r.date,' ',ts.end_time), '%Y-%m-%d %H:%i:%s') <= ?", [$now])
            ->select('r.id')
            ->get();

        if ($expired->isEmpty()) {
            $this->info('No hay reservas caducadas.');
            return 0;
        }

        $ids = $expired->pluck('id')->toArray();

        DB::transaction(function () use ($ids) {
            DB::table('reservations')
                ->whereIn('id', $ids)
                ->update([
                    'status' => 'pendiente_devolucion',
                    'updated_at' => now(),
                ]);
        });

        $this->info('Reservas marcadas como pendiente_devolucion: ' . count($ids));
        return 0;
    }
}
