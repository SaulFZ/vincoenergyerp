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
    /**
     * Muestra el historial de peticiones de descarga masiva al SAT.
     */
    public function index()
    {
        $requests = SatRequest::orderBy('created_at', 'desc')->paginate(20);

        // Lógica movida al controlador: Verificamos si ya hay una petición hoy
        $today = Carbon::now()->format('Y-m-d');
        $hasRequestToday = SatRequest::where('request_date', $today)->exists();

        return view('modules.administration.expense-claims.sat-requests-log', [
            'requests'        => $requests,
            'hasRequestToday' => $hasRequestToday
        ]);
    }

    /**
     * Permite forzar una sincronización manual del día en curso
     * por si el CRON Job falló o se requiere actualizar de inmediato.
     */
    public function forceSync(Request $request)
    {
        $today = Carbon::now()->format('Y-m-d');

        // 1. VALIDACIÓN: Revisar si ya existe cualquier registro del día de hoy
        $requestToday = SatRequest::where('request_date', $today)->first();

        if ($requestToday) {
            if ($requestToday->status === 'pending') {
                return redirect()->back()->with('warning', 'Ya existe una sincronización en curso para el día de hoy. Espere a que el SAT termine de procesarla.');
            } else {
                return redirect()->back()->with('info', 'La sincronización masiva de hoy ya fue completada exitosamente.');
            }
        }

        // 2. Revisar que exista un certificado válido
        $node = FslNode::where('is_live', true)->first();

        if (!$node) {
            return redirect()->back()->with('error', 'No existe un certificado activo configurado en el sistema para realizar la petición.');
        }

        // 3. Crear la solicitud y despachar el Job
        $satRequest = SatRequest::create([
            'request_date' => $today,
            'status'       => 'pending',
        ]);

        RequestFiscalDownloadJob::dispatch($node->g_id, $today, $today, $satRequest->id);

        return redirect()->back()->with('success', 'Sincronización manual enviada a la cola. El SAT procesará la solicitud en breve.');
    }
}
