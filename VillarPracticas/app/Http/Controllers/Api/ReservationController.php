<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationCreated;
use App\Models\usuaris;

class ReservationController extends Controller
{
    public function store(Request $request)
    {
        // Validación del payload
        $validated = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:usuaris,id'],
            'date' => ['required', 'date_format:Y-m-d'],
            'slots' => ['required', 'array', 'min:1'],
            'slots.*.slot_id' => ['required', 'integer', 'exists:time_slots,id'],
            'slots.*.items' => ['required', 'array', 'min:1'],
            'slots.*.items.*.resource_id' => ['required', 'integer', 'exists:resources,id'],
            'slots.*.items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $date  = $validated['date'];
        $slots = $validated['slots'];

        // Obtener usuario: preferimos sesión, y si no, request
        $userId = session('usuari_id') ?? ($validated['user_id'] ?? null);

        if (!$userId) {
            return response()->json([
                'ok' => false,
                'message' => 'Sesión no válida. Vuelve a iniciar sesión.'
            ], 401);
        }

        return DB::transaction(function () use ($date, $slots, $userId) {

            $createdReservations = [];

            foreach ($slots as $slot) {
                $slotId = (int) $slot['slot_id'];
                $items  = $slot['items'];

                // 1) Bloqueo del stock del slot (evita overbooking)
                $slotResources = DB::table('slot_resources')
                    ->where('time_slot_id', $slotId)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('resource_id');

                if ($slotResources->isEmpty()) {
                    throw ValidationException::withMessages([
                        'slots' => ["La franja $slotId no tiene recursos asociados."]
                    ]);
                }

                // 2) Validación de stock por recurso y fecha + slot
                foreach ($items as $item) {
                    $resourceId = (int) $item['resource_id'];
                    $qty        = (int) $item['quantity'];

                    if (!$slotResources->has($resourceId)) {
                        throw ValidationException::withMessages([
                            'slots' => ["El recurso $resourceId no está disponible en la franja $slotId."]
                        ]);
                    }

                    $totalAvailable = (int) $slotResources[$resourceId]->available_units;

                    $alreadyReserved = (int) DB::table('reservation_items as ri')
                        ->join('reservations as r', 'r.id', '=', 'ri.reservation_id')
                        ->where('r.date', $date)
                        ->where('r.time_slot_id', $slotId)
                        ->where('ri.resource_id', $resourceId)
                        ->lockForUpdate()
                        ->sum('ri.quantity');

                    $remaining = $totalAvailable - $alreadyReserved;

                    if ($qty > $remaining) {
                        throw ValidationException::withMessages([
                            'slots' => ["No hay stock suficiente del recurso $resourceId en la franja $slotId. Quedan $remaining."]
                        ]);
                    }
                }

                // 3) Crear reservation (una por slot)
                $reservationId = DB::table('reservations')->insertGetId([
                    'user_id' => $userId,
                    'date' => $date,
                    'time_slot_id' => $slotId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 4) Crear items
                foreach ($items as $item) {
                    DB::table('reservation_items')->insert([
                        'reservation_id' => $reservationId,
                        'resource_id' => (int) $item['resource_id'],
                        'quantity' => (int) $item['quantity'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $createdReservations[] = [
                    'reservation_id' => $reservationId,
                    'time_slot_id' => $slotId,
                    'items' => $items,
                ];
            }

            return response()->json([
                'ok' => true,
                'message' => 'Reserva creada correctamente.',
                'reservations' => $createdReservations
            ], 201);
        });
    }

    public function destroy($id)
{
    $userId = session('usuari_id');

    if (!$userId) {
        return response()->json([
            'ok' => false,
            'message' => 'Sesión no válida'
        ], 401);
    }

    return DB::transaction(function () use ($id, $userId) {

        // Comprobamos que la reserva es del usuario
        $reservation = DB::table('reservations')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$reservation) {
            return response()->json([
                'ok' => false,
                'message' => 'Reserva no encontrada'
            ], 404);
        }

        // 1) Borrar items
        DB::table('reservation_items')
            ->where('reservation_id', $id)
            ->delete();

        // 2) Borrar reserva
        DB::table('reservations')
            ->where('id', $id)
            ->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Reserva anulada correctamente'
        ]);
    });
}

}
