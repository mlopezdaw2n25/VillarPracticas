<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationCreatedMail;
use App\Models\usuaris;

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

                    // 🔥 SOLO BLOQUEAN LAS ACTIVAS
                    $alreadyReserved = (int) DB::table('reservation_items as ri')
                        ->join('reservations as r', 'r.id', '=', 'ri.reservation_id')
                        ->where('r.date', $date)
                        ->where('r.time_slot_id', $slotId)
                        ->where('r.status', 'activa')
                        ->where('ri.resource_id', $resourceId)
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
                    'status' => 'activa',
                    'return_defectuoso' => false,
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
       SOLICITAR DEVOLUCIÓN
    ====================================================== */

    public function destroy($id)
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

        if ($reservation->status !== 'activa') {
            return response()->json([
                'ok' => false,
                'message' => 'Solo reservas activas pueden devolverse.'
            ], 400);
        }

        DB::table('reservations')
            ->where('id', $id)
            ->update([
                'status' => 'pendiente_devolucion',
                'updated_at' => now()
            ]);

        return response()->json([
            'ok' => true,
            'message' => 'Devolución solicitada correctamente.'
        ]);
    }

    /* ======================================================
       CONFIRMAR DEVOLUCIÓN
    ====================================================== */

    public function confirmReturn(Request $request, $id)
    {
        $userId = session('usuari_id');

        if (!$userId) {
            return response()->json(['ok' => false], 401);
        }

        $validated = $request->validate([
            'incident' => ['required', 'boolean'],
            'comment'  => ['nullable', 'string']
        ]);

        $reservation = DB::table('reservations')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$reservation) {
            return response()->json(['ok' => false], 404);
        }

        if ($reservation->status !== 'pendiente_devolucion') {
            return response()->json([
                'ok' => false,
                'message' => 'La reserva no está pendiente de devolución.'
            ], 400);
        }

        DB::table('reservations')
            ->where('id', $id)
            ->update([
                'status' => 'finalizada',
                'return_defectuoso' => $validated['incident'],
                'return_comentario' => $validated['comment'],
                'updated_at' => now()
            ]);

        return response()->json([
            'ok' => true,
            'message' => 'Reserva finalizada correctamente.'
        ]);
    }

    /* ======================================================
       MOSTRAR RESERVA
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
                'status' => $reservation->status,
                'return_defectuoso' => $reservation->return_defectuoso,
                'return_comentario' => $reservation->return_comentario,
                'items' => $items
            ]
        ]);
    }

    /* ======================================================
       ACTUALIZAR MATERIALES (CANTIDADES)
    ====================================================== */

    public function updateItems(Request $request, $id)
    {
        $userId = session('usuari_id');

        if (!$userId) {
            return response()->json(['ok' => false], 401);
        }

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.resource_id' => ['required', 'integer', 'exists:resources,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $reservation = DB::table('reservations')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$reservation) {
            return response()->json(['ok' => false], 404);
        }

        if ($reservation->status !== 'activa') {
            return response()->json([
                'ok' => false,
                'message' => 'Solo reservas activas pueden modificarse.'
            ], 400);
        }

        $items = collect($validated['items'])
            ->map(function ($i) {
                return [
                    'resource_id' => (int) $i['resource_id'],
                    'quantity' => (int) $i['quantity'],
                ];
            })
            ->values();

        // Evita duplicados de resource_id en el payload
        if ($items->count() !== $items->pluck('resource_id')->unique()->count()) {
            throw ValidationException::withMessages([
                'items' => ['No se permiten recursos duplicados.']
            ]);
        }

        $existingIds = DB::table('reservation_items')
            ->where('reservation_id', $id)
            ->pluck('resource_id')
            ->map(fn ($v) => (int) $v)
            ->values();

        if ($existingIds->isEmpty()) {
            return response()->json([
                'ok' => false,
                'message' => 'La reserva no tiene recursos para modificar.'
            ], 400);
        }

        $incomingIds = $items->pluck('resource_id')->sort()->values();
        $existingSorted = $existingIds->sort()->values();

        if ($incomingIds->count() !== $existingSorted->count() || $incomingIds->values()->all() !== $existingSorted->values()->all()) {
            throw ValidationException::withMessages([
                'items' => ['Solo puedes modificar cantidades; no puedes añadir ni eliminar recursos.']
            ]);
        }

        return DB::transaction(function () use ($reservation, $id, $items) {
            $slotId = (int) $reservation->time_slot_id;
            $date = $reservation->date;

            // Bloqueamos stock del slot para los recursos implicados
            $slotResources = DB::table('slot_resources')
                ->where('time_slot_id', $slotId)
                ->whereIn('resource_id', $items->pluck('resource_id')->all())
                ->lockForUpdate()
                ->get()
                ->keyBy('resource_id');

            if ($slotResources->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => ['No hay stock configurado para esta franja.']
                ]);
            }

            foreach ($items as $item) {
                $resourceId = (int) $item['resource_id'];
                $qty = (int) $item['quantity'];

                if (!$slotResources->has($resourceId)) {
                    throw ValidationException::withMessages([
                        'items' => ["El recurso $resourceId no está disponible en esta franja."]
                    ]);
                }

                $totalAvailable = (int) $slotResources[$resourceId]->available_units;

                // Stock ocupado por OTRAS reservas activas (excluimos la actual)
                $reservedByOthers = (int) DB::table('reservation_items as ri')
                    ->join('reservations as r', 'r.id', '=', 'ri.reservation_id')
                    ->where('r.date', $date)
                    ->where('r.time_slot_id', $slotId)
                    ->where('r.status', 'activa')
                    ->where('ri.resource_id', $resourceId)
                    ->where('r.id', '!=', $id)
                    ->sum('ri.quantity');

                $remaining = $totalAvailable - $reservedByOthers;

                if ($qty > $remaining) {
                    throw ValidationException::withMessages([
                        'items' => ["Stock insuficiente para el recurso $resourceId. Quedan $remaining."]
                    ]);
                }
            }

            foreach ($items as $item) {
                DB::table('reservation_items')
                    ->where('reservation_id', $id)
                    ->where('resource_id', (int) $item['resource_id'])
                    ->update([
                        'quantity' => (int) $item['quantity'],
                        'updated_at' => now(),
                    ]);
            }

            DB::table('reservations')
                ->where('id', $id)
                ->update([
                    'updated_at' => now(),
                ]);

            return response()->json([
                'ok' => true,
                'message' => 'Reserva modificada correctamente.'
            ]);
        });
    }
}