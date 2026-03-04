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

            $resourceIds = $totals->pluck('id')->map(fn ($v) => (int) $v)->all();

            // Última devolución finalizada por recurso (si existe)
            $lastReturnsMap = collect();

            if (!empty($resourceIds)) {
                $latestByResource = DB::table('reservation_items as ri')
                    ->join('reservations as r', 'r.id', '=', 'ri.reservation_id')
                    ->where('r.status', 'finalizada')
                    ->whereIn('ri.resource_id', $resourceIds)
                    ->select([
                        'ri.resource_id',
                        DB::raw('MAX(r.updated_at) as last_return_at'),
                    ])
                    ->groupBy('ri.resource_id');

                $lastReturns = DB::table('reservation_items as ri')
                    ->join('reservations as r', 'r.id', '=', 'ri.reservation_id')
                    ->joinSub($latestByResource, 'lr', function ($join) {
                        $join->on('lr.resource_id', '=', 'ri.resource_id')
                            ->on('lr.last_return_at', '=', 'r.updated_at');
                    })
                    ->where('r.status', 'finalizada')
                    ->whereIn('ri.resource_id', $resourceIds)
                    ->select([
                        'ri.resource_id',
                        'r.return_defectuoso',
                        'r.updated_at as last_return_at',
                    ])
                    ->get();

                $lastReturnsMap = $lastReturns
                    ->groupBy('resource_id')
                    ->map(function ($rows) {
                        // Si por casualidad hay duplicados, nos quedamos con el más reciente
                        return $rows->sortByDesc('last_return_at')->first();
                    });
            }

            // Reservados ya en esa fecha/franja
            $reserved = DB::table('reservation_items as ri')
                ->join('reservations as r', 'r.id', '=', 'ri.reservation_id')
                ->where('r.date', $date)
                ->where('r.time_slot_id', $slotId)
                ->select('ri.resource_id', DB::raw('COALESCE(SUM(ri.quantity),0) as reserved_units'))
                ->groupBy('ri.resource_id')
                ->pluck('reserved_units', 'resource_id');

            // remaining = total - reservado
            $resources = $totals->map(function ($row) use ($reserved, $lastReturnsMap) {
                $already = (int) ($reserved[$row->id] ?? 0);
                $remaining = max((int)$row->available_units - $already, 0);

                $last = $lastReturnsMap->get((int) $row->id);
                $lastDefectuoso = $last ? (bool) $last->return_defectuoso : null;

                return [
                    'id' => (int)$row->id,
                    'name' => $row->name,
                    'type' => $row->type,
                    'remaining' => $remaining,
                    'last_return_defectuoso' => $lastDefectuoso,
                    'last_return_status' => $lastDefectuoso === null
                        ? null
                        : ($lastDefectuoso ? 'con_incidencia' : 'sin_incidencias'),
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
