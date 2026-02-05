<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AllReservationsController extends Controller
{
    public function index()
    {
        // Seguridad: solo usuarios logueados
        if (!session('usuari_id')) {
            return response()->json(['message' => 'No autorizado'], 401);
        }

        $rows = DB::table('reservations as r')
            ->join('usuaris as u', 'u.id', '=', 'r.user_id')
            ->join('time_slots as ts', 'ts.id', '=', 'r.time_slot_id')
            ->leftJoin('reservation_items as ri', 'ri.reservation_id', '=', 'r.id')
            ->leftJoin('resources as res', 'res.id', '=', 'ri.resource_id')
            ->select(
                'r.id',
                'r.date',
                'ts.start_time',
                'ts.end_time',
                'u.nom as user_name',
                'res.name as resource_name',
                'res.type as resource_type',
                'ri.quantity'
            )
            ->orderBy('r.date', 'desc')
            ->get();

        // Agrupar
        $grouped = [];

        foreach ($rows as $row) {
            if (!isset($grouped[$row->id])) {
                $grouped[$row->id] = [
                    'id' => $row->id,
                    'date' => $row->date,
                    'start_time' => $row->start_time,
                    'end_time' => $row->end_time,
                    'user' => $row->user_name,
                    'items' => []
                ];
            }

            if ($row->resource_name) {
                $grouped[$row->id]['items'][] = [
                    'name' => $row->resource_name,
                    'type' => $row->resource_type,
                    'quantity' => $row->quantity
                ];
            }
        }

        return array_values($grouped);
    }
}