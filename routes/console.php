<?php

use Illuminate\Support\Facades\Schedule;
use App\Models\Administration\ExpenseClaims\SatRequest;
use App\Models\Administration\ExpenseClaims\FslNode;
use App\Jobs\Administration\ExpenseClaims\RequestFiscalDownloadJob;
use App\Jobs\Administration\ExpenseClaims\VerifyFiscalDownloadJob;
use Carbon\Carbon;

// ── 1. PROCESO DE MEDIANOCHE: VENTANA MÓVIL DE 15 DÍAS ──
Schedule::call(function () {
    // Si ya hay algo pendiente, no satures al SAT
    if (SatRequest::where('status', 'pending')->exists()) {
        return;
    }

    $hoy = Carbon::now();
    $haceQuinceDias = Carbon::now()->subDays(15);
    $fechaRegistro = $hoy->format('Y-m-d');

    // Aquí usamos la columna 'type' que ya configuramos
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
                $haceQuinceDias->format('Y-m-d') . 'T00:00:00',
                $hoy->format('Y-m-d') . 'T23:59:59',
                $satRequest->id
            );
        }
    }
})->dailyAt('00:00');

// ── 2. PROCESO MENSUAL: BARRIDO DE LOS ÚLTIMOS 2 MESES ──
Schedule::call(function () {
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
        RequestFiscalDownloadJob::dispatch(
            $node->g_id,
            $dosMesesAtrasInicio->format('Y-m-d') . 'T00:00:00',
            $mesAnteriorFin->format('Y-m-d') . 'T23:59:59',
            $satRequest->id
        );
    }
})->monthlyOn(3, '01:00');

// ── 3. PROCESO CONTINUO: REVISAR CADA 30 MINUTOS ──
// Cambiado de everyTwoHours() a everyThirtyMinutes() para mayor agilidad
Schedule::call(function () {
    $pendingDownload = SatRequest::where('status', 'pending')
        ->whereNotNull('ticket_id')
        ->orderBy('created_at', 'asc') // Procesar primero el más antiguo
        ->first();

    if ($pendingDownload) {
        VerifyFiscalDownloadJob::dispatch(
            $pendingDownload->ticket_id,
            $pendingDownload->id
        );
    }
})->everyThirtyMinutes();
