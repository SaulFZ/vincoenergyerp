<?php

namespace App\Http\Controllers\Systems\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Systems\Tickets\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TicketStoreController extends Controller
{
    /**
     * Procesa la creación de un nuevo ticket desde el portal de usuario.
     * Implementa validación estricta y transacciones ACID para evitar huérfanos.
     */
    public function store(Request $request)
    {
        // 1. Validación Minimalista
        $request->validate([
            'area_code'   => 'required|string|max:10',
            'subject'     => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        try {
            // 2. Transacción de Base de Datos (ACID Compliance)
            return DB::transaction(function () use ($request) {

                $code = strtoupper(trim($request->area_code));

                // 3. Obtenemos el prefijo corto de Año y Mes (Ej: '2605' para Mayo 2026)
                $yearMonth = now()->format('ym');

                // 4. Contamos los tickets del mes actual para esa área.
                // Usamos withTrashed() para respetar el constraint unique() de la BD
                // si es que se llegan a usar SoftDeletes en los tickets.
                $count = Ticket::withTrashed()
                    ->where('department_code', $code)
                    ->whereYear('created_at', now()->year)
                    ->whereMonth('created_at', now()->month)
                    ->count();

                // 5. Generación del Folio. Resultado: SIS2605-01
                $folio = $code . $yearMonth . '-' . str_pad($count + 1, 2, '0', STR_PAD_LEFT);

                // 6. Inserción con Default Triage
                $ticket = Ticket::create([
                    'folio'           => $folio,
                    'department_code' => $code,
                    'user_id'         => auth()->id(),
                    'subject'         => $request->subject,
                    'description'     => $request->description,
                    'priority'        => 'sin clasificar',
                    'status'          => 'nuevo',
                ]);

                // 7. Respuesta JSON Inmediata
                return response()->json([
                    'success' => true,
                    'message' => 'El ticket ha sido registrado en la cola de soporte.',
                    'folio'   => $folio
                ], 201);
            });

        } catch (\Exception $e) {
            // 8. Registro Silencioso de Errores Críticos (Log adaptado para Vinco ERP)
            Log::critical('[Vinco ERP] Error en creación de Ticket: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'payload' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Fallo de integridad al registrar el ticket. El equipo técnico ha sido notificado.'
            ], 500);
        }
    }
}
