<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ListadoController extends Controller
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

        // obtenemos slots seleccionados
        $slots = DB::table('time_slots')
            ->whereIn('id', $slotIds)
            ->orderBy('start_time')
            ->get();

        // Resources asociados a esos slots:
        // Devolvemos el stock disponible por fecha+slot+resource (para enseñar solo lo disponible)
        $resourcesBySlot = [];

        foreach ($slotIds as $slotId) {
            // total unidades disponibles en esa franja
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

            // unidades ya reservadas en esa fecha+slot
            $reserved = DB::table('reservation_items as ri')
                ->join('reservations as r', 'r.id', '=', 'ri.reservation_id')
                ->where('r.date', $date)
                ->where('r.time_slot_id', $slotId)
                ->select('ri.resource_id', DB::raw('SUM(ri.quantity) as reserved_units'))
                ->groupBy('ri.resource_id')
                ->pluck('reserved_units', 'resource_id');

            // calculamos remaining y filtramos los que están agotados
            $resources = $totals->map(function ($row) use ($reserved) {
                $already = (int) ($reserved[$row->id] ?? 0);
                $remaining = max((int)$row->available_units - $already, 0);

                return [
                    'id' => $row->id,
                    'name' => $row->name,
                    'type' => $row->type,
                    'remaining' => $remaining
                ];
            })->filter(fn ($r) => $r['remaining'] > 0)->values();

            $resourcesBySlot[$slotId] = $resources;
        }

        return view('listado', compact('date', 'slots', 'resourcesBySlot'));
    }
}
