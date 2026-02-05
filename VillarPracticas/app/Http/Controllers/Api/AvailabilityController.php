<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AvailabilityController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $date = $request->query('date');

        // Slots base
        $timeSlots = DB::table('time_slots')
            ->orderBy('start_time')
            ->get();

        $result = [];

        foreach ($timeSlots as $ts) {

            // recursos disponibles en ese slot
            $resources = DB::table('slot_resources as sr')
                ->join('resources as r', 'r.id', '=', 'sr.resource_id')
                ->where('sr.time_slot_id', $ts->id)
                ->where('r.active', 1)
                ->select([
                    'sr.resource_id',
                    'sr.available_units',
                ])
                ->get();

            // reservados en fecha+slot por recurso
            $reserved = DB::table('reservation_items as ri')
                ->join('reservations as res', 'res.id', '=', 'ri.reservation_id')
                ->where('res.date', $date)
                ->where('res.time_slot_id', $ts->id)
                ->select('ri.resource_id', DB::raw('SUM(ri.quantity) as qty'))
                ->groupBy('ri.resource_id')
                ->pluck('qty', 'resource_id');

            // calcular remaining por recurso
            $availableResources = 0;
            $totalRemainingUnits = 0;

            foreach ($resources as $row) {
                $already = (int) ($reserved[$row->resource_id] ?? 0);
                $remaining = max((int)$row->available_units - $already, 0);

                $totalRemainingUnits += $remaining;
                if ($remaining > 0) $availableResources++;
            }

            // ✅ El slot se desactiva SOLO si ya no queda ningún recurso con stock
            $disabled = ($availableResources === 0);

            $result[] = [
                'slot_id' => (int)$ts->id,
                'start_time' => $ts->start_time,
                'end_time' => $ts->end_time,
                'start_datetime' => Carbon::parse("$date {$ts->start_time}")->format('Y-m-d H:i:s'),
                'end_datetime' => Carbon::parse("$date {$ts->end_time}")->format('Y-m-d H:i:s'),

                // info útil:
                'resources_available' => $availableResources,     // cuantos recursos aún tienen stock
                'remaining_units' => $totalRemainingUnits,       // suma de unidades restantes
                'disabled' => $disabled,
            ];
        }

        return response()->json([
            'date' => $date,
            'slots' => $result,
        ]);
    }
}
