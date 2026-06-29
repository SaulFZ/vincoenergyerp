<?php

use Illuminate\Support\Facades\Schedule;
use App\Models\Administration\ExpenseClaims\SatRequest;
use App\Models\Administration\ExpenseClaims\FslNode;
use App\Jobs\Administration\ExpenseClaims\RequestFiscalDownloadJob;
use App\Jobs\Administration\ExpenseClaims\VerifyFiscalDownloadJob;
use Carbon\Carbon;

// ── 1. PROCESO DE MEDIANOCHE: VENTANA MÓVIL DE 1 SEMANA (7 DÍAS) ──
Schedule::call(function () {
    if (SatRequest::where('status', 'pending')->exists()) {
        return;
    }

    // Forzamos la zona horaria de México para evitar que Dokploy mande hora de Londres (UTC)
    $hoy = Carbon::now('America/Mexico_City');
    $ayer = $hoy->copy()->subDay(); // Ayer
    $haceUnaSemana = $hoy->copy()->subDays(7); // Hace 7 días

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
                $haceUnaSemana->format('Y-m-d') . 'T00:00:00',
                // EL TRUCO ESTÁ AQUÍ: Pedimos hasta las 23:59:59 de AYER. 100% en el pasado.
                $ayer->format('Y-m-d') . 'T23:59:59',
                $satRequest->id
            );
        }
    }
})->dailyAt('00:00');

// ── 2. PROCESO MENSUAL: BARRIDO COMPLETO DEL MES ANTERIOR ──
// Corre el día 3 de cada mes para atrapar cualquier rezago o cancelación extrema
Schedule::call(function () {
    $mesAnteriorInicio = Carbon::now()->subMonth()->startOfMonth();
    $mesAnteriorFin      = Carbon::now()->subMonth()->endOfMonth();
    $fechaRegistro       = Carbon::now()->format('Y-m-d');

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
            // El fin de mes anterior siempre está en el pasado, el 23:59:59 es seguro aquí
            $mesAnteriorFin->format('Y-m-d') . 'T23:59:59',
            $satRequest->id
        );
    }
})->monthlyOn(3, '01:00');


// ── 3. PROCESO CONTINUO: VERIFICADOR CADA 30 MINUTOS ──
Schedule::call(function () {
    $pendingDownload = SatRequest::where('status', 'pending')
        ->whereNotNull('ticket_id')
        ->orderBy('created_at', 'asc')
        ->first();

    if ($pendingDownload) {
        VerifyFiscalDownloadJob::dispatch(
            $pendingDownload->ticket_id,
            $pendingDownload->id
        );
    }
})->everyThirtyMinutes();
