<?php
namespace App\Http\Controllers\Administration\ExpenseClaims;

use App\Http\Controllers\Controller;
use App\Jobs\Administration\ExpenseClaims\RequestFiscalDownloadJob;
use App\Models\Administration\ExpenseClaims\FslNode;
use App\Models\Administration\ExpenseClaims\SatRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SatRequestsController extends Controller
{
    public function index()
    {
        // 1. Verificamos si ya existe una petición generada hoy en proceso
        // Esto desactiva el botón de "Sincronización Manual" en la vista.
        $hasRequestToday = SatRequest::whereDate('request_date', now('America/Mexico_City')->format('Y-m-d'))
            ->where('status', 'pending')
            ->exists();

        // 2. Obtenemos el historial paginado estricto a 10 registros por página
        $requests = SatRequest::orderBy('created_at', 'desc')->paginate(10);

        return view('modules.administration.expense-claims.sat-requests-log', [
            'hasRequestToday' => $hasRequestToday,
            'requests'        => $requests,
        ]);
    }

    public function forceSync(Request $request)
    {
        // 1. CANDADO ESTRICTO: Bloquear si ya hay una sincronización corriendo
        $pendingRequest = SatRequest::where('status', 'pending')->first();
        if ($pendingRequest) {
            return redirect()->back()->with('warning', "Operación bloqueada. Ya existe una sincronización en curso (ID: {$pendingRequest->id}). Espere a que finalice.");
        }

        // 2. Revisar el certificado activo del nodo FSL
        $node = FslNode::where('is_live', true)->first();
        if (! $node) {
            return redirect()->back()->with('error', 'No existe un certificado activo configurado en el sistema.');
        }

        // 3. Forzamos la zona horaria a Ciudad de México
        $hoy = Carbon::now('America/Mexico_City');
        $todayString = $hoy->format('Y-m-d');

        // 4. Crear la solicitud en BD con la etiqueta 'Manual'
        $satRequest = SatRequest::create([
            'request_date' => $todayString,
            'status'       => 'pending',
            'type'         => 'Manual',
        ]);

        // 5. Inicio: Buscamos 7 días hacia atrás
        $startDate = $hoy->copy()->subDays(7)->format('Y-m-d\TH:i:s');

        // 6. Fin: Le restamos 10 minutos a la hora actual para evitar el desface del reloj del SAT
        $endDate = $hoy->copy()->subMinutes(10)->format('Y-m-d\TH:i:s');

        // 7. Despachar el Job inicial a la cola
        RequestFiscalDownloadJob::dispatch($node->g_id, $startDate, $endDate, $satRequest->id);

        return redirect()->back()->with('success', 'Sincronización manual enviada a la cola exitosamente.');
    }
}
