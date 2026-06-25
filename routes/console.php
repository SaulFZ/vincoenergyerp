<?php

use Illuminate\Support\Facades\Schedule;
use App\Models\Administration\ExpenseClaims\SatRequest;
use App\Models\Administration\ExpenseClaims\FslNode;
use App\Jobs\Administration\ExpenseClaims\RequestFiscalDownloadJob;
use App\Jobs\Administration\ExpenseClaims\VerifyFiscalDownloadJob;
use Carbon\Carbon;

// ── 1. PROCESO DE MEDIANOCHE: VENTANA MÓVIL DE 15 DÍAS ──
Schedule::call(function () {
    if (SatRequest::where('status', 'pending')->exists()) {
        return;
    }

    $hoy = Carbon::now();
    $haceQuinceDias = Carbon::now()->subDays(15); // <--- Ajustado a 15 días
    $fechaRegistro = $hoy->format('Y-m-d');

    $alreadyExists = SatRequest::where('request_date', $fechaRegistro)->where('type', 'daily')->exists();

    if (!$alreadyExists) {
        $satRequest = SatRequest::create([
            'request_date' => $fechaRegistro,
            'status'       => 'pending',
            'type'         => 'daily'
        ]);

        $node = FslNode::where('is_live', true)->first();

        if ($node) {
            $startDate = $haceQuinceDias->format('Y-m-d') . 'T00:00:00';
            $endDate   = $hoy->format('Y-m-d') . 'T23:59:59';

            RequestFiscalDownloadJob::dispatch(
                $node->g_id,
                $startDate,
                $endDate,
                $satRequest->id
            );
        }
    }
})->dailyAt('00:00');

// ── 2. PROCESO MENSUAL: BARRIDO DE LOS ÚLTIMOS 2 MESES ──
// Se ejecuta el día 3 de cada mes a la 1:00 AM
Schedule::call(function () {
    // Ejemplo si corre el 3 de Julio: Traerá desde el 1 de Mayo hasta el 30 de Junio
    $dosMesesAtrasInicio = Carbon::now()->subMonths(2)->startOfMonth();
    $mesAnteriorFin      = Carbon::now()->subMonth()->endOfMonth();
    $fechaRegistro       = Carbon::now()->format('Y-m-d');

    $satRequest = SatRequest::create([
        'request_date' => $fechaRegistro,
        'status'       => 'pending',
        'type'         => 'monthly'
    ]);

    $node = FslNode::where('is_live', true)->first();

    if ($node) {
        $startDate = $dosMesesAtrasInicio->format('Y-m-d') . 'T00:00:00';
        $endDate   = $mesAnteriorFin->format('Y-m-d') . 'T23:59:59';

        RequestFiscalDownloadJob::dispatch(
            $node->g_id,
            $startDate,
            $endDate,
            $satRequest->id
        );
    }
})->monthlyOn(3, '01:00');

// ── 3. PROCESO CONTINUO: REVISAR CADA 2 HORAS SI HAY DESCARGAS PENDIENTES ──
Schedule::call(function () {
    $pendingDownload = SatRequest::where('status', 'pending')
        ->whereNotNull('ticket_id')
        ->first();

    if ($pendingDownload) {
        VerifyFiscalDownloadJob::dispatch(
            $pendingDownload->ticket_id,
            $pendingDownload->id
        );
    }
})->everyTwoHours();
