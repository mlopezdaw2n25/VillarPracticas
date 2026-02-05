<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurgeExpiredReservations extends Command
{
    protected $signature = 'reservations:purge-expired';
    protected $description = 'Elimina automáticamente reservas caducadas (ya finalizadas)';

    public function handle()
    {
        $now = now()->format('Y-m-d H:i:s');

        // Selecciona reservas cuyo final ya pasó:
        // end_datetime = CONCAT(date, ' ', time_slots.end_time)
        $expired = DB::table('reservations as r')
            ->join('time_slots as ts', 'ts.id', '=', 'r.time_slot_id')
            ->whereRaw("STR_TO_DATE(CONCAT(r.date,' ',ts.end_time), '%Y-%m-%d %H:%i:%s') <= ?", [$now])
            ->select('r.id')
            ->get();

        if ($expired->isEmpty()) {
            $this->info('No hay reservas caducadas.');
            return 0;
        }

        $ids = $expired->pluck('id')->toArray();

        DB::transaction(function () use ($ids) {
            DB::table('reservation_items')->whereIn('reservation_id', $ids)->delete();
            DB::table('reservations')->whereIn('id', $ids)->delete();
        });

        $this->info('Reservas eliminadas: ' . count($ids));
        return 0;
    }
}
