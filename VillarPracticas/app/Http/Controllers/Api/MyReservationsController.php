<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MyReservationsController extends Controller
{
    public function index()
{
    $userId = session('usuari_id');

    if (!$userId) {
        return response()->json([], 401);
    }

    $reservations = DB::table('reservations as r')
        ->join('time_slots as ts', 'ts.id', '=', 'r.time_slot_id')
        ->where('r.user_id', $userId)
        ->orderByDesc('r.date')
        ->orderByDesc('ts.start_time')
        ->select(
            'r.id',
            'r.date',
            'r.status',
            'ts.start_time',
            'ts.end_time'
        )
        ->get();

    $result = [];

    foreach ($reservations as $r) {

        $reservationEnd = \Carbon\Carbon::parse(
            $r->date . ' ' . $r->end_time
        );

        $status = $r->status;

        // 🔥 CAMBIO AUTOMÁTICO (PERSISTENTE)
        if ($status === 'activa' && now()->greaterThan($reservationEnd)) {
            DB::table('reservations')
                ->where('id', $r->id)
                ->where('status', 'activa')
                ->update([
                    'status' => 'pendiente_devolucion',
                    'updated_at' => now(),
                ]);

            $status = 'pendiente_devolucion';
        }

        $items = DB::table('reservation_items as ri')
            ->join('resources as res', 'res.id', '=', 'ri.resource_id')
            ->where('ri.reservation_id', $r->id)
            ->select(
                'ri.resource_id',
                'ri.quantity',
                'res.name',
                'res.type'
            )
            ->get();

        $result[] = [
            'id' => $r->id,
            'date' => $r->date,
            'start_time' => $r->start_time,
            'end_time' => $r->end_time,
            'status' => $status, // 👈 importante
            'items' => $items
        ];
    }

    return response()->json($result);
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

        $reservation = DB::table('reservations')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$reservation) {
            return response()->json(['message' => 'Reserva no encontrada'], 404);
        }

        if ($reservation->status !== 'activa') {
            return response()->json([
                'message' => 'Solo reservas activas pueden devolverse.'
            ], 400);
        }

        DB::table('reservations')
            ->where('id', $id)
            ->update([
                'status' => 'pendiente_devolucion',
                'updated_at' => now()
            ]);

        return response()->json(['ok' => true]);
    }
}
