<?php

use Illuminate\Support\Facades\Schedule;
use App\Models\Administration\ExpenseClaims\SatRequest;
use App\Models\Administration\ExpenseClaims\FslNode;
use App\Jobs\Administration\ExpenseClaims\RequestFiscalDownloadJob;
use App\Jobs\Administration\ExpenseClaims\VerifyFiscalDownloadJob;
use Carbon\Carbon;

// ── 1. PROCESO DIARIO: SOLO EL DÍA ANTERIOR (SIN TRASLAPES) ──
Schedule::call(function () {
    // Evitar accionar si hay peticiones pendientes masivas
    if (SatRequest::where('status', 'pending')->count() > 3) {
        return;
    }

    // Forzamos la zona horaria para precisión exacta
    $hoy = Carbon::now('America/Mexico_City');
    $ayer = $hoy->copy()->subDay(); // Solo tomamos ayer

    $fechaRegistro = $hoy->format('Y-m-d');

    $alreadyExists = SatRequest::where('request_date', $fechaRegistro)
                               ->where('type', 'daily')
                               ->exists();

    if (!$alreadyExists) {
        $satRequest = SatRequest::create([
            'request_date' => $fechaRegistro,
            'status'       => 'pending',
            'type'         => 'daily'
        ]);

        $node = FslNode::where('is_live', true)->first();

        if ($node) {
            RequestFiscalDownloadJob::dispatch(
                $node->g_id,
                $ayer->format('Y-m-d') . 'T00:00:00', // EXACTAMENTE desde las 00:00 de ayer
                $ayer->format('Y-m-d') . 'T23:59:59', // HASTA las 23:59 de ayer (0 traslapes)
                $satRequest->id
            );
        }
    }
})->dailyAt('01:30'); // Lo movemos a la 1:30 AM para asegurar que los servidores del SAT ya cerraron el día previo

// ── 2. PROCESO MENSUAL: BARRIDO COMPLETO DEL MES ANTERIOR ──
// Corre el día 3 de cada mes para atrapar facturas desfasadas
Schedule::call(function () {
    $mesAnteriorInicio = Carbon::now()->subMonth()->startOfMonth();
    $mesAnteriorFin    = Carbon::now()->subMonth()->endOfMonth();
    $fechaRegistro     = Carbon::now()->format('Y-m-d');

    $satRequest = SatRequest::create([
        'request_date' => $fechaRegistro,
        'status'       => 'pending',
        'type'         => 'monthly'
    ]);

    $node = FslNode::where('is_live', true)->first();

    if ($node) {
        RequestFiscalDownloadJob::dispatch(
            $node->g_id,
            $mesAnteriorInicio->format('Y-m-d') . 'T00:00:00',
            $mesAnteriorFin->format('Y-m-d') . 'T23:59:59',
            $satRequest->id
        );
    }
})->monthlyOn(3, '02:00');


// ── 3. PROCESO CONTINUO: VERIFICADOR MULTI-TICKET (Cada 30 min) ──
Schedule::call(function () {
    // Extraemos TODOS los pendientes (no solo el ->first) para evitar cuellos de botella
    $pendingDownloads = SatRequest::where('status', 'pending')
        ->whereNotNull('ticket_id')
        ->orderBy('created_at', 'asc')
        ->get();

    foreach ($pendingDownloads as $pending) {
        VerifyFiscalDownloadJob::dispatch(
            $pending->ticket_id,
            $pending->id
        );
    }
})->everyThirtyMinutes();
