<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationCreatedMail;
use App\Models\usuaris;
use Carbon\Carbon;

class ReservationController extends Controller
{

    /* ======================================================
       CREAR RESERVA
    ====================================================== */

    public function store(Request $request)
    {
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

        $userId = session('usuari_id') ?? ($validated['user_id'] ?? null);

        if (!$userId) {
            return response()->json([
                'ok' => false,
                'message' => 'Sesión no válida.'
            ], 401);
        }

        return DB::transaction(function () use ($date, $slots, $userId) {

            $createdReservations = [];

            foreach ($slots as $slot) {

                $slotId = (int) $slot['slot_id'];
                $items  = $slot['items'];

                $slotResources = DB::table('slot_resources')
                    ->where('time_slot_id', $slotId)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('resource_id');

                if ($slotResources->isEmpty()) {
                    throw ValidationException::withMessages([
                        'slots' => ["La franja $slotId no tiene recursos."]
                    ]);
                }

                foreach ($items as $item) {

                    $resourceId = (int) $item['resource_id'];
                    $qty        = (int) $item['quantity'];

                    if (!$slotResources->has($resourceId)) {
                        throw ValidationException::withMessages([
                            'slots' => ["Recurso no disponible en esta franja."]
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
                            'slots' => ["Stock insuficiente. Quedan $remaining."]
                        ]);
                    }
                }

                $reservationId = DB::table('reservations')->insertGetId([
                    'user_id' => $userId,
                    'date' => $date,
                    'time_slot_id' => $slotId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

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

            /* ================= EMAIL ================= */

            $user = usuaris::find($userId);

            if ($user && $user->email) {

                $userName =
                    $user->name ??
                    $user->nom ??
                    $user->nombre ??
                    $user->username ??
                    '';

                $emailData = [];

                foreach ($createdReservations as $res) {

                    $slot = DB::table('time_slots')
                        ->where('id', $res['time_slot_id'])
                        ->first();

                    $itemsDetailed = [];

                    foreach ($res['items'] as $item) {

                        $resource = DB::table('resources')
                            ->where('id', $item['resource_id'])
                            ->first();

                        $itemsDetailed[] = [
                            'name' => $resource->name ?? 'Recurso',
                            'quantity' => $item['quantity']
                        ];
                    }

                    $emailData[] = [
                        'slot_time' =>
                            substr($slot->start_time, 0, 5) .
                            ' - ' .
                            substr($slot->end_time, 0, 5),
                        'items' => $itemsDetailed
                    ];
                }

                $user->display_name = $userName;

                Mail::to($user->email)
                    ->send(new ReservationCreatedMail($user, $date, $emailData));
            }

            return response()->json([
                'ok' => true,
                'message' => 'Reserva creada correctamente.',
                'reservations' => $createdReservations
            ], 201);
        });
    }

    /* ======================================================
       ELIMINAR RESERVA
    ====================================================== */

    public function destroy($id)
    {
        $userId = session('usuari_id');

        if (!$userId) {
            return response()->json(['ok' => false], 401);
        }

        return DB::transaction(function () use ($id, $userId) {

            $reservation = DB::table('reservations')
                ->where('id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$reservation) {
                return response()->json(['ok' => false], 404);
            }

            DB::table('reservation_items')
                ->where('reservation_id', $id)
                ->delete();

            DB::table('reservations')
                ->where('id', $id)
                ->delete();

            return response()->json([
                'ok' => true,
                'message' => 'Reserva anulada correctamente'
            ]);
        });
    }

    /* ======================================================
       MODIFICAR ITEMS (DEVUELVE STOCK AUTOMÁTICO)
    ====================================================== */

    public function updateItems(Request $request, $id)
    {
        $userId = session('usuari_id');

        if (!$userId) {
            return response()->json(['ok' => false], 401);
        }

        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.resource_id' => ['required', 'integer', 'exists:resources,id'],
            'items.*.quantity' => ['required', 'integer', 'min:0'],
        ]);

        return DB::transaction(function () use ($id, $userId, $validated) {

            $reservation = DB::table('reservations')
                ->where('id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$reservation) {
                return response()->json(['ok' => false], 404);
            }

            $slot = DB::table('time_slots')
                ->where('id', $reservation->time_slot_id)
                ->first();

            $reservationStart = Carbon::parse(
                $reservation->date . ' ' . $slot->start_time
            );

            if (now()->greaterThanOrEqualTo($reservationStart)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'La franja ya ha comenzado.'
                ], 400);
            }

            foreach ($validated['items'] as $item) {

                $resourceId = $item['resource_id'];
                $newQty = (int) $item['quantity'];

                if ($newQty === 0) {
                    DB::table('reservation_items')
                        ->where('reservation_id', $id)
                        ->where('resource_id', $resourceId)
                        ->delete();
                    continue;
                }

                $slotResource = DB::table('slot_resources')
                    ->where('time_slot_id', $reservation->time_slot_id)
                    ->where('resource_id', $resourceId)
                    ->first();

                if (!$slotResource) continue;

                $totalAvailable = (int) $slotResource->available_units;

                $alreadyReserved = (int) DB::table('reservation_items as ri')
                    ->join('reservations as r', 'r.id', '=', 'ri.reservation_id')
                    ->where('r.date', $reservation->date)
                    ->where('r.time_slot_id', $reservation->time_slot_id)
                    ->where('ri.resource_id', $resourceId)
                    ->where('ri.reservation_id', '!=', $id)
                    ->sum('ri.quantity');

                $remaining = $totalAvailable - $alreadyReserved;

                if ($newQty > $remaining) {
                    return response()->json([
                        'ok' => false,
                        'message' => 'Stock insuficiente.'
                    ], 400);
                }

                DB::table('reservation_items')
                    ->updateOrInsert(
                        [
                            'reservation_id' => $id,
                            'resource_id' => $resourceId
                        ],
                        [
                            'quantity' => $newQty,
                            'updated_at' => now()
                        ]
                    );
            }

            return response()->json([
                'ok' => true,
                'message' => 'Reserva actualizada correctamente'
            ]);
        });
    }

    /* ======================================================
       MOSTRAR RESERVA (PARA MODAL)
    ====================================================== */

    public function show($id)
    {
        $userId = session('usuari_id');

        if (!$userId) {
            return response()->json(['ok' => false], 401);
        }

        $reservation = DB::table('reservations')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$reservation) {
            return response()->json(['ok' => false], 404);
        }

        $items = DB::table('reservation_items as ri')
            ->join('resources as r', 'r.id', '=', 'ri.resource_id')
            ->where('ri.reservation_id', $id)
            ->select('ri.resource_id', 'ri.quantity', 'r.name', 'r.type')
            ->get();

        return response()->json([
            'ok' => true,
            'reservation' => [
                'id' => $reservation->id,
                'date' => $reservation->date,
                'time_slot_id' => $reservation->time_slot_id,
                'items' => $items
            ]
        ]);
    }
}
