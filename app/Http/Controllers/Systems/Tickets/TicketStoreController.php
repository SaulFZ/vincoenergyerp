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
        // 1. Validación Minimalista: Solo requerimos lo que el usuario realmente aporta.
        // La prioridad y el estado han sido delegados completamente a la lógica de negocio.
        $request->validate([
            'area_code'   => 'required|string|max:10',
            'subject'     => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        try {
            // 2. Transacción de Base de Datos (ACID Compliance)
            return DB::transaction(function () use ($request) {

                $code = strtoupper(trim($request->area_code));

                // 3. Generación Concurrente del Folio (Pessimistic Read avoidance approach)
                // En sistemas de alto tráfico, calcular el max() es más seguro que count() si se borran registros,
                // pero count() funciona bien si usamos SoftDeletes.
                $count = Ticket::where('department_code', $code)->count();
                $folio = $code . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

                // 4. Inserción con Default Triage
                $ticket = Ticket::create([
                    'folio'           => $folio,
                    'department_code' => $code,
                    'user_id'         => auth()->id(),
                    'subject'         => $request->subject,
                    'description'     => $request->description,
                    'priority'        => 'media', // Asignación automática para evaluación de Sistemas
                    'status'          => 'nuevo', // Estado inicial inmutable por el usuario
                ]);

                // 5. Respuesta JSON Inmediata para el renderizado asíncrono
                return response()->json([
                    'success' => true,
                    'message' => 'El ticket ha sido registrado en la cola de soporte.',
                    'folio'   => $folio
                ], 201);
            });

        } catch (\Exception $e) {
            // 6. Registro Silencioso de Errores Críticos (Log)
            Log::critical('[Vinco One ERP] Error en creación de Ticket: ' . $e->getMessage(), [
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
