<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ListadoController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'date'  => ['required', 'date_format:Y-m-d'],
            'slots' => ['required', 'string'],
        ]);

        $date = $request->query('date');

        $id = session('usuari_id');

        $slotIds = collect(explode(',', $request->query('slots')))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values()
            ->all();

        /* =====================================================
           OBTENER FRANJAS
        ===================================================== */

        $slots = DB::table('time_slots')
            ->whereIn('id', $slotIds)
            ->orderBy('start_time')
            ->get();

        $resourcesBySlot = [];

        foreach ($slotIds as $slotId) {

            /* =====================================================
               TOTAL DISPONIBLE EN SLOT
            ===================================================== */

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
                        return $rows->sortByDesc('last_return_at')->first();
                    });
            }

            /* =====================================================
               RESERVAS ACTIVAS (CONFIRMADAS)
            ===================================================== */

            $reservedConfirmed = DB::table('reservation_items as ri')
                ->join('reservations as r', 'r.id', '=', 'ri.reservation_id')
                ->where('r.date', $date)
                ->where('r.time_slot_id', $slotId)
                ->where('r.status', 'activa') // SOLO activas bloquean confirmado
                ->select('ri.resource_id', DB::raw('SUM(ri.quantity) as reserved_units'))
                ->groupBy('ri.resource_id')
                ->pluck('reserved_units', 'resource_id');

            /* =====================================================
               RESERVAS PENDIENTES (NO CONFIRMADAS)
            ===================================================== */

            $reservedPending = DB::table('reservation_items as ri')
                ->join('reservations as r', 'r.id', '=', 'ri.reservation_id')
                ->where('r.date', $date)
                ->where('r.time_slot_id', $slotId)
                ->where('r.status', 'pendiente_devolucion')
                ->select('ri.resource_id', DB::raw('SUM(ri.quantity) as pending_units'))
                ->groupBy('ri.resource_id')
                ->pluck('pending_units', 'resource_id');

            /* =====================================================
               CALCULAR STOCK REAL
            ===================================================== */

            $resources = $totals->map(function ($row) use ($reservedConfirmed, $reservedPending) {

                $confirmed = (int) ($reservedConfirmed[$row->id] ?? 0);
                $pending   = (int) ($reservedPending[$row->id] ?? 0);

                $remaining = max((int)$row->available_units - $confirmed, 0);

                return [
                    'id' => $row->id,
                    'name' => $row->name,
                    'type' => $row->type,
                    'remaining' => $remaining,

                    // indicador visual para el círculo
                    'status_indicator' => $pending > 0 ? 'pendiente' : 'confirmado'
                ];
            })
            ->filter(fn ($r) => $r['remaining'] > 0)
            ->values();

            // Añadimos info de última devolución (incidencias) al array final
            $resources = $resources->map(function ($r) use ($lastReturnsMap) {
                $last = $lastReturnsMap->get((int) $r['id']);
                $lastDefectuoso = $last ? (bool) $last->return_defectuoso : null;
                $r['last_return_defectuoso'] = $lastDefectuoso;
                $r['last_return_status'] = $lastDefectuoso === null
                    ? null
                    : ($lastDefectuoso ? 'con_incidencia' : 'sin_incidencias');
                return $r;
            });

            $resourcesBySlot[$slotId] = $resources;
        }

        return view('Profesors.listado', compact(
            'date',
            'slots',
            'resourcesBySlot',
            'id'
        ));
    }
}