<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MyReservationsController extends Controller
{
    public function index(Request $request)
    {
        $userId = session('usuari_id');

        if (!$userId) {
            return response()->json([
                'ok' => false,
                'message' => 'Sesión no válida'
            ], 401);
        }

        $reservations = DB::table('reservations as r')
            ->join('time_slots as ts', 'ts.id', '=', 'r.time_slot_id')
            ->where('r.user_id', $userId)
            ->orderByDesc('r.date')
            ->orderBy('ts.start_time')
            ->select([
                'r.id',
                'r.date',
                'ts.start_time',
                'ts.end_time',
            ])
            ->get();

        $items = DB::table('reservation_items as ri')
            ->join('resources as res', 'res.id', '=', 'ri.resource_id')
            ->whereIn('ri.reservation_id', $reservations->pluck('id'))
            ->select([
                'ri.reservation_id',
                'res.name',
                'res.type',
                'ri.quantity',
            ])
            ->get()
            ->groupBy('reservation_id');

        $out = $reservations->map(function ($r) use ($items) {
            return [
                'id' => $r->id,
                'date' => $r->date,
                'start_time' => $r->start_time,
                'end_time' => $r->end_time,
                'items' => ($items[$r->id] ?? collect())->values(),
            ];
        });

        return response()->json($out);
    }

    public function update(Request $request, $id)
    {
        $userId = session('usuari_id');

        if (!$userId) {
            return response()->json(['message' => 'Sesión no válida'], 401);
        }

        $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'time_slot_id' => ['required', 'integer', 'exists:time_slots,id'],
        ]);

        $exists = DB::table('reservations')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->exists();

        if (!$exists) {
            return response()->json(['message' => 'Reserva no encontrada'], 404);
        }

        DB::table('reservations')
            ->where('id', $id)
            ->update([
                'date' => $request->date,
                'time_slot_id' => $request->time_slot_id,
                'updated_at' => now(),
            ]);

        return response()->json(['ok' => true]);
    }

    public function destroy($id)
    {
        $userId = session('usuari_id');

        if (!$userId) {
            return response()->json(['message' => 'Sesión no válida'], 401);
        }

        $exists = DB::table('reservations')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->exists();

        if (!$exists) {
            return response()->json(['message' => 'Reserva no encontrada'], 404);
        }

        DB::transaction(function () use ($id) {
            DB::table('reservation_items')->where('reservation_id', $id)->delete();
            DB::table('reservations')->where('id', $id)->delete();
        });

        return response()->json(['ok' => true]);
    }
}
