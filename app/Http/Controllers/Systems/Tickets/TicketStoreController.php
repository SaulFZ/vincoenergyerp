<?php

namespace App\Http\Controllers\Systems\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Systems\Tickets\Ticket;
use App\Models\Auth\User;
use App\Mail\System\Tickets\NewTicketAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TicketStoreController extends Controller
{
    /**
     * Procesa la creación de un nuevo ticket desde el portal de usuario.
     * Implementa validación estricta y transacciones ACID.
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
            // =========================================================
            // 2. Transacción de Base de Datos (ACID Compliance)
            // =========================================================
            $ticket = DB::transaction(function () use ($request) {

                $code = strtoupper(trim($request->area_code));
                $yearMonth = now()->format('ym');

                $count = Ticket::withTrashed()
                    ->where('department_code', $code)
                    ->whereYear('created_at', now()->year)
                    ->whereMonth('created_at', now()->month)
                    ->count();

                $folio = $code . $yearMonth . '-' . str_pad($count + 1, 2, '0', STR_PAD_LEFT);

                return Ticket::create([
                    'folio'           => $folio,
                    'department_code' => $code,
                    'user_id'         => auth()->id(),
                    'subject'         => $request->subject,
                    'description'     => $request->description,
                    'priority'        => 'sin clasificar',
                    'status'          => 'nuevo',
                ]);
            });

            // =========================================================
            // 3. ENVIAR CORREO AL EQUIPO DE SISTEMAS / SOPORTE
            // =========================================================
            try {
                // Extraer los correos de los usuarios activos que tienen permiso para atender tickets
                // Replicando la estructura exacta de permisos directos
                $recipientEmails = User::active()
                    ->whereHas('directPermissions', function ($query) {
                        $query->where('name', 'atender_tickets');
                    })
                    ->pluck('email')
                    ->filter()
                    ->toArray();

                if (!empty($recipientEmails)) {
                    Mail::to($recipientEmails)->send(new NewTicketAlert($ticket));
                }
            } catch (\Exception $mailEx) {
                // Registro silencioso del error de correo sin deshacer el registro del ticket
                Log::error('🚨 [Vinco ERP] Error enviando alerta de nuevo ticket: ' . $mailEx->getMessage());
            }

            // =========================================================
            // 4. RESPONDER AL NAVEGADOR
            // =========================================================
            return response()->json([
                'success' => true,
                'message' => 'El ticket ha sido registrado en la cola de soporte.',
                'folio'   => $ticket->folio
            ], 201);

        } catch (\Exception $e) {
            // Log crítico si falla la base de datos
            Log::critical('🚨 [Vinco ERP] Error crítico en creación de Ticket: ' . $e->getMessage(), [
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
