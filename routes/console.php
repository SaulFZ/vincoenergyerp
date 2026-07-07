<?php

use Illuminate\Support\Facades\Schedule;
use App\Models\Administration\ExpenseClaims\SatRequest;
use App\Models\Administration\ExpenseClaims\FslNode;
use App\Jobs\Administration\ExpenseClaims\RequestFiscalDownloadJob;
use App\Jobs\Administration\ExpenseClaims\VerifyFiscalDownloadJob;
use Carbon\Carbon;

// ── 0. LIMPIEZA DE TICKETS FANTASMA DEL SAT ──
// El SAT descarta los tickets a las 72 horas. Nosotros los marcaremos como fallidos a las 48hrs
// para evitar que bloqueen las nuevas peticiones.
Schedule::call(function () {
    SatRequest::where('status', 'pending')
        ->where('created_at', '<', Carbon::now()->subHours(48))
        ->update(['status' => 'failed']);
})->dailyAt('00:30');

// ── 1. PROCESO DIARIO: SOLO EL DÍA ANTERIOR (SIN TRASLAPES) ──
Schedule::call(function () {
    if (SatRequest::where('status', 'pending')->count() > 3) {
        return;
    }

    $hoy = Carbon::now('America/Mexico_City');
    $ayer = $hoy->copy()->subDay();

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
                $ayer->format('Y-m-d') . 'T00:00:00',
                $ayer->format('Y-m-d') . 'T23:59:59',
                $satRequest->id
            );
        }
    }
})->dailyAt('01:30');

// ── 2. PROCESO MENSUAL: BARRIDO COMPLETO DEL MES ANTERIOR ──
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
