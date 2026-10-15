<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('expense_claims', function (Blueprint $table) {
            $table->id();

            // ── FOLIOS Y FECHA ──
            $table->string('folio_system')->unique()->comment('Folio principal generado por el sistema, ej: VES-01');
            $table->string('folio_user')->nullable()->comment('Folio interno o siglas del usuario solicitante');
            $table->date('claim_date')->comment('Fecha del registro de la comprobación');

            // ── TIPOS Y CATEGORÍAS ──
            $table->string('request_type')->default('Reembolso')->comment('Reembolso, Comprobacion Electronica, Comprobacion Directa');
            $table->string('category')->index()->comment('viaje, operacion, otros');

            // ── CONTROL FISCAL ──
            $table->boolean('is_deductible')->default(true)->comment('Indicador de deducibilidad fiscal global del expediente');

            // ── TRAZABILIDAD, UBICACIÓN Y RELACIONES ──
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->comment('Colaborador beneficiario del recurso');
            $table->foreignId('created_by_id')->constrained('users')->cascadeOnDelete()->comment('Usuario en sesión que captura la solicitud');

            $table->foreignId('expense_advance_id')->nullable()->constrained('expense_advances')->nullOnDelete()->comment('Vínculo al anticipo amortizado');

            // Vínculo referencial al anticipo de origen (solo para Reembolso por excedente)
            $table->foreignId('related_advance_id')->nullable()->constrained('expense_advances')->nullOnDelete()->comment('Vínculo referencial al anticipo de origen');

            $table->string('area')->nullable()->comment('Área o departamento de adscripción');
            $table->string('emission_place')->default('VHSA, TAB.')->comment('Plaza o ubicación física de emisión');

            // ── IMPUTACIÓN CONTABLE A NIVEL CABECERA ──
            $table->foreignId('cost_center_id')
                  ->nullable()
                  ->constrained('cost_centers')
                  ->restrictOnDelete()
                  ->comment('Centro de costos global. Si es NULL, la asignación es multilínea (VARIOS)');

            $table->foreignId('project_id')
                  ->nullable()
                  ->constrained('projects')
                  ->restrictOnDelete()
                  ->comment('Proyecto operativo asociado al centro de costos');

            // ── MOTIVO Y CONSOLIDACIÓN FINANCIERA ──
            $table->string('motive')->comment('Justificación u objeto del gasto');
            $table->decimal('total_subtotal', 20, 2)->default(0)->comment('Base gravable total consolidada');
            $table->decimal('total_iva', 20, 2)->default(0)->comment('Impuesto al Valor Agregado consolidado');
            $table->decimal('total_ish', 20, 2)->default(0)->comment('Impuestos locales y hospedaje consolidados');
            $table->decimal('total_amount', 20, 2)->comment('Importe final neto comprobado');

            // ── GESTIÓN DE SOBRANTES (INTEGRADO SIN FLAG DE VERIFICACIÓN) ──
            $table->decimal('leftover_amount', 25, 2)->nullable()->comment('Sobrante del anticipo a devolver');
            $table->string('leftover_method')->nullable()->comment('Método: transferencia');
            $table->string('leftover_proof_path')->nullable()->comment('Ruta del PDF del comprobante');
            $table->text('leftover_notes')->nullable()->comment('Comentarios o cuenta destino de la devolución');

            // ── GESTOR DOCUMENTAL ──
            $table->json('evidence_documents')->nullable()->comment('Rutas de almacenamiento de comprobantes PDF');

            // ── MÁQUINA DE ESTADOS (TRIPLE DESACOPLADA) ──
            $table->string('status_review')->default('pendiente')->index()->comment('Revisión Técnica/Fiscal: borrador, pendiente, validado, rechazado');
            $table->string('status_approval')->default('en_espera')->index()->comment('Aprobación Gerencial: n_a, en_espera, en_revision, aprobado, rechazado');
                $table->string('status_payment')->default('no_aplica')->index()->comment('Estado de liquidación en tesorería. no_aplica, no_pagado, pagado.');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_claims');
    }
};
