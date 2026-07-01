<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_claims', function (Blueprint $table) {
            $table->id();

            // ── FOLIOS Y FECHA ──
            $table->string('folio_system')->unique()->comment('Folio principal, ej: SIS1206-01');
            $table->string('folio_user')->nullable()->comment('Folio interno del usuario');
            $table->date('claim_date')->comment('Fecha del Documento (modal-fecha-hoy)');
            $table->string('category')->index()->comment('Viaje, Operacion, Otros');

            // ── CAMPO NUEVO: CONTROL FISCAL ──
            $table->boolean('is_deductible')->default(true)->comment('Flag para control fiscal global del reembolso');

            // ── TRAZABILIDAD Y UBICACIÓN ──
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->comment('Beneficiario');
            $table->foreignId('created_by_id')->constrained('users')->cascadeOnDelete()->comment('Capturista en sesión');
            $table->string('area')->nullable()->comment('Área de adscripción');
            $table->string('cost_center')->index()->comment('Centro de Costos (Imputación)');
            $table->string('emission_place')->default('VHSA, TAB.')->comment('Lugar de Emisión');

            // ── DETALLES Y TOTALES CONSOLIDADOS ──
            $table->string('motive')->comment('Motivo de la erogación');
            $table->decimal('total_subtotal', 20, 2)->default(0)->comment('Suma Erogada (Base)');
            $table->decimal('total_iva', 20, 2)->default(0)->comment('Impuesto (I.V.A.) global');
            $table->decimal('total_ish', 20, 2)->default(0)->comment('Impuestos Locales (I.S.H.) global');
            $table->decimal('total_amount', 20, 2)->comment('Gran total a reembolsar líquido');

            // ── GESTOR DOCUMENTAL ──
            $table->json('evidence_documents')->nullable()->comment('Arreglo JSON con rutas a PDFs');

            // ── ESTADOS DE REVISIÓN Y PAGO ──
            $table->string('status_review')->index()->comment('Borrador, Pendiente, Validado, Aprobado, Rechazado');
            $table->string('status_payment')->index()->comment('N/A, En espera, Por autorizar, Por pagar, Pagado, No procede');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_claims');
    }
};
