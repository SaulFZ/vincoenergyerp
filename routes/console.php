<?php

use Illuminate\Support\Facades\Schedule;
use App\Models\Administration\ExpenseClaims\SatRequest;
use App\Models\Administration\ExpenseClaims\FslNode;
use App\Jobs\Administration\ExpenseClaims\RequestFiscalDownloadJob;
use App\Jobs\Administration\ExpenseClaims\VerifyFiscalDownloadJob;

/*
|--------------------------------------------------------------------------
| PLANIFICADOR CENTRALIZADO DE TAREAS (CRON JOBS)
|--------------------------------------------------------------------------
| Orquestación de procesos asíncronos y descargas del SAT.
*/

// ── 1. PROCESO DE MEDIANOCHE: DISPARAR LA SOLICITUD DIARIA ──
Schedule::call(function () {
    $today = now()->format('Y-m-d');

    // Evitamos duplicar la solicitud si el comando se corre dos veces
    $alreadyExists = SatRequest::where('request_date', $today)->exists();

    if (!$alreadyExists) {
        // Creamos la bitácora en estado pendiente
        $satRequest = SatRequest::create([
            'request_date' => $today,
            'status' => 'pending',
        ]);

        // Obtenemos el nodo RFC emisor activo de la empresa
        $node = FslNode::where('is_live', true)->first();

        if ($node) {
            // Despachamos el Job 1 pasándole las fechas de hoy y el ID de nuestra bitácora
            RequestFiscalDownloadJob::dispatch($node->g_id, $today, $today, $satRequest->id);
        }
    }
})->dailyAt('00:00');


// ── 2. PROCESO CONTINUO: REVISAR CADA 2 HORAS SI HAY DESCARGAS PENDIENTES ──
Schedule::call(function () {
    // Buscamos si existe alguna solicitud pendiente que ya tenga asignado un ticket_id
    $pendingDownload = SatRequest::where('status', 'pending')
        ->whereNotNull('ticket_id')
        ->first();

    // SI EXISTE: Mandamos al Job 2 a preguntar al SAT.
    // SI NO EXISTE: No hace nada, el servidor descansa.
    if ($pendingDownload) {
        VerifyFiscalDownloadJob::dispatch($pendingDownload->ticket_id, $pendingDownload->id);
    }
})->everyTwoHours();
