<?php

use Illuminate\Support\Facades\Schedule;
use App\Models\Administration\ExpenseClaims\SatRequest;
use App\Models\Administration\ExpenseClaims\FslNode;
use App\Jobs\Administration\ExpenseClaims\RequestFiscalDownloadJob;
use App\Jobs\Administration\ExpenseClaims\VerifyFiscalDownloadJob;

// ── 1. PROCESO DE MEDIANOCHE: DISPARAR LA SOLICITUD DIARIA ──
Schedule::call(function () {
    $today = now()->format('Y-m-d');
    $alreadyExists = SatRequest::where('request_date', $today)->exists();

    if (!$alreadyExists) {
        $satRequest = SatRequest::create([
            'request_date' => $today,
            'status' => 'pending',
        ]);
        $node = FslNode::where('is_live', true)->first();

        if ($node) {
            RequestFiscalDownloadJob::dispatch($node->g_id, $today, $today, $satRequest->id);
        }
    }
})->dailyAt('00:00');

// ── 2. PROCESO CONTINUO: REVISAR CADA 2 HORAS SI HAY DESCARGAS PENDIENTES ──
Schedule::call(function () {
    $pendingDownload = SatRequest::where('status', 'pending')->whereNotNull('ticket_id')->first();
    if ($pendingDownload) {
        VerifyFiscalDownloadJob::dispatch($pendingDownload->ticket_id, $pendingDownload->id);
    }
})->everyTwoHours();
