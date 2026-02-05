<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ListadoDataController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'slots' => ['required', 'string'],
        ]);

        $date = $request->query('date');

        $slotIds = collect(explode(',', $request->query('slots')))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values()
            ->all();

        // Slots seleccionados
        $slots = DB::table('time_slots')
            ->whereIn('id', $slotIds)
            ->orderBy('start_time')
            ->get()
            ->map(fn($s) => [
                'slot_id' => (int) $s->id,
                'start_time' => $s->start_time,
                'end_time' => $s->end_time,
            ]);

        // Recursos disponibles por slot
        $resourcesBySlot = [];

        foreach ($slotIds as $slotId) {

            $totals = DB::table('slot_resources as sr')
                ->join('resources as res', 'res.id', '=', 'sr.resource_id')
                ->where('sr.time_slot_id', $slotId)
                ->where('res.active', true)
                ->select([
                    'res.id',
                    'res.name',
                    'res.type',
                    'sr.available_units',
                ])
                ->get();

            // Reservados ya en esa fecha/franja
            $reserved = DB::table('reservation_items as ri')
                ->join('reservations as r', 'r.id', '=', 'ri.reservation_id')
                ->where('r.date', $date)
                ->where('r.time_slot_id', $slotId)
                ->select('ri.resource_id', DB::raw('COALESCE(SUM(ri.quantity),0) as reserved_units'))
                ->groupBy('ri.resource_id')
                ->pluck('reserved_units', 'resource_id');

            // remaining = total - reservado
            $resources = $totals->map(function ($row) use ($reserved) {
                $already = (int) ($reserved[$row->id] ?? 0);
                $remaining = max((int)$row->available_units - $already, 0);

                return [
                    'id' => (int)$row->id,
                    'name' => $row->name,
                    'type' => $row->type,
                    'remaining' => $remaining,
                ];
            })->values();

            $resourcesBySlot[(string)$slotId] = $resources;
        }

        return response()->json([
            'date' => $date,
            'slots' => $slots,
            'resourcesBySlot' => $resourcesBySlot,
        ]);
    }
}
