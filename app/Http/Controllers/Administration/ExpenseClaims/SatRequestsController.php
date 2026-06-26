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
        // 1. Verificamos si ya existe una petición hoy usando la clase correcta: SatRequest
        $hasRequestToday = SatRequest::whereDate('request_date', now()->format('Y-m-d'))
                                    ->exists();

        // 2. Obtenemos el historial paginado usando la clase correcta: SatRequest
        $requests = SatRequest::orderBy('created_at', 'desc')->paginate(15);

        return view('modules.administration.expense-claims.sat-requests-log', [
            'hasRequestToday' => $hasRequestToday,
            'requests'        => $requests
        ]);
    }

    public function forceSync(Request $request)
    {
        // 1. CANDADO ESTRICTO
        $pendingRequest = SatRequest::where('status', 'pending')->first();
        if ($pendingRequest) {
            return redirect()->back()->with('warning', "Operación bloqueada. Ya existe una sincronización en curso (ID: {$pendingRequest->id}).");
        }

        // 2. CANDADO DE DÍA
        $today = Carbon::now()->format('Y-m-d');
        $todayCompleted = SatRequest::where('request_date', $today)->where('status', 'completed')->exists();
        if ($todayCompleted) {
            return redirect()->back()->with('info', 'La sincronización de hoy ya fue completada.');
        }

        // 3. Revisar certificado
        $node = FslNode::where('is_live', true)->first();
        if (!$node) {
            return redirect()->back()->with('error', 'No existe un certificado activo.');
        }

        // 4. Crear la solicitud
        $satRequest = SatRequest::create([
            'request_date' => $today,
            'status'       => 'pending',
            'type'         => 'manual',
        ]);

        // ─── LA CORRECCIÓN MÁGICA AQUÍ ───
        // Le damos un inicio y fin de día exacto para que el SAT lo acepte
        $startDate = $today . ' 00:00:00';
        $endDate   = $today . ' 23:59:59';

        // Despachar el Job con las fechas corregidas (usando el RFC del nodo, no el ID)
        RequestFiscalDownloadJob::dispatch($node->g_id, $startDate, $endDate, $satRequest->id);

        return redirect()->back()->with('success', 'Sincronización manual enviada a la cola exitosamente.');
    }}
