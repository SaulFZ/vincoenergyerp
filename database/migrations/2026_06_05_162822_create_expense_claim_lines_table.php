<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_claim_lines', function (Blueprint $table) {
            $table->id();

            // ── LLAVES FORÁNEAS ──
            $table->foreignId('expense_claim_id')->constrained('expense_claims')->cascadeOnDelete();

            // Enlace con la bóveda fiscal (si la fila proviene de una factura válida)
            $table->foreignId('expense_cfdi_id')->nullable()->constrained('expense_cfdis')->nullOnDelete();

            // ── ORIGEN DE DATOS Y AUDITORÍA ──
            $table->string('load_method', 50)
                  ->index()
                  ->comment('Origen de los datos: manual, boveda_uuid (búsqueda en SAT), boveda_xml (arrastre de archivo)');

            // ── AGRUPACIÓN Y FECHA ──
            $table->string('concept_group')->index()->comment('cat-vuelos, cat-restaurantes, cat-combustible, cat-otros');
            $table->date('expense_date')->index()->comment('Fecha del gasto');
            $table->string('document_number')->nullable()->comment('Folio/Num. Fac. Comercial');
            $table->string('description')->comment('Descripción Comercial');

            // ── COLUMNAS DE IMPORTES SUBTOTALES ──
            $table->decimal('amount_fiscal', 20, 2)->default(0.00)->comment('Comp. Fiscal (PDF + XML)');
            $table->decimal('amount_simple', 20, 2)->default(0.00)->comment('Comp. Simple No Fiscal');
            $table->decimal('amount_none', 20, 2)->default(0.00)->comment('Sin Comp. y Propinas');

            // ── IMPUESTOS Y TOTAL DE LA FILA ──
            $table->decimal('tax_ish', 20, 2)->default(0.00)->comment('I.S.H. / Otros Imp.');
            $table->decimal('tax_iva', 20, 2)->default(0.00)->comment('I.V.A.');
            $table->decimal('line_total', 20, 2)->comment('Suma horizontal de la fila');

            // ── CONTROL INTERNO CONTABLE ──
            $table->boolean('is_deductible')->default(true)->comment('Marcador para contable');
            $table->string('accounting_account')->nullable()->comment('Cuenta contable asignada');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_claim_lines');
    }
};
