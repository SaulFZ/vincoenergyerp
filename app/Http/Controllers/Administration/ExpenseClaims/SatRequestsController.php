<?php

namespace App\Http\Controllers\Administration\ExpenseClaims;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Administration\ExpenseClaims\SatRequest;
use App\Models\Administration\ExpenseClaims\FslNode;
use App\Jobs\Administration\ExpenseClaims\RequestFiscalDownloadJob;
use Carbon\Carbon;

class SatRequestsController extends Controller
{
    public function index()
    {
        // 1. Verificamos si ya existe una petición generada de forma automática (daily) o manual en proceso
        $hasRequestToday = SatRequest::whereDate('request_date', now()->format('Y-m-d'))
                                    ->where('status', 'pending')
                                    ->exists();

        // 2. Obtenemos el historial paginado ordenado por el más reciente
        $requests = SatRequest::orderBy('created_at', 'desc')->paginate(15);

        return view('modules.administration.expense-claims.sat-requests-log', [
            'hasRequestToday' => $hasRequestToday,
            'requests'        => $requests
        ]);
    }

    public function forceSync(Request $request)
    {
        // 1. CANDADO ESTRICTO: Bloquear solo si ya hay una sincronización corriendo en este mismo momento
        $pendingRequest = SatRequest::where('status', 'pending')->first();
        if ($pendingRequest) {
            return redirect()->back()->with('warning', "Operación bloqueada. Ya existe una sincronización en curso (ID: {$pendingRequest->id}). Espere a que finalice.");
        }

        // 2. Revisar el certificado activo del nodo FSL
        $node = FslNode::where('is_live', true)->first();
        if (!$node) {
            return redirect()->back()->with('error', 'No existe un certificado activo configurado en el sistema.');
        }

        $hoy = Carbon::now();
        $todayString = $hoy->format('Y-m-d');

        // 3. Crear la solicitud en BD con la etiqueta 'manual' para nuestra trazabilidad
        $satRequest = SatRequest::create([
            'request_date' => $todayString,
            'status'       => 'pending',
            'type'         => 'manual',
        ]);

        // ─── LA CORRECCIÓN MÁGICA AQUÍ ───

        // Inicio: Buscamos 7 días hacia atrás igual que el CRON, por si la factura que les urge es de ayer o antier.
        $haceUnaSemana = Carbon::now()->subDays(7)->format('Y-m-d');
        $startDate = $haceUnaSemana . 'T00:00:00';

        // Fin: La hora y segundo EXACTOS del clic. Ni un segundo en el futuro para que el SAT lo acepte.
        $endDate   = $hoy->format('Y-m-d\TH:i:s');

        // 4. Despachar el Job con las fechas perfectas
        RequestFiscalDownloadJob::dispatch($node->g_id, $startDate, $endDate, $satRequest->id);

        return redirect()->back()->with('success', 'Sincronización manual enviada a la cola exitosamente. En breve se reflejarán los resultados.');
    }
}
