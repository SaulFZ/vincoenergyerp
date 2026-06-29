<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_cfdis', function (Blueprint $table) {
            $table->id();

            // ── RELACIÓN CON LA BÓVEDA ──
            $table->foreignId('fsl_node_id')->nullable()->constrained('fsl_nodes')->nullOnDelete();

            // ── DATOS FISCALES DEL SAT Y COMPROBANTE ──
            $table->uuid('uuid')->unique()->comment('Folio Fiscal del SAT');
            $table->string('serie', 100)->nullable();
            $table->string('folio', 100)->nullable();

            $table->string('issuer_rfc', 30)->index()->comment('RFC del proveedor/empleado');
            $table->string('issuer_name')->nullable()->comment('Razón social del emisor');

            $table->string('receiver_rfc', 25)->index()->comment('RFC receptor');
            $table->string('receiver_name', 255)->nullable();

            // ── MONTOS E IMPUESTOS ──
            $table->decimal('subtotal', 25, 2);
            $table->decimal('total', 25, 2);
            $table->decimal('tax_iva', 25, 2)->default(0.00);
            $table->decimal('tax_ish', 25, 2)->default(0.00);
            $table->decimal('tax_retenciones', 25, 2)->default(0.00);
            $table->string('currency', 3)->comment('Moneda (MXN, USD, etc.)');

            // ── FECHAS Y CLASIFICACIÓN DEL CFDI ──
            $table->dateTime('issue_date')->comment('Fecha y hora de emisión del comprobante');
            $table->string('cfdi_type', 15)->nullable()->comment('I=Ingreso, E=Egreso, P=Pago');
            $table->string('payment_method', 20)->nullable()->comment('PUE, PPD');
            $table->string('payment_form', 30)->nullable()->comment('01, 03, 04, 99...');
            $table->string('use_cfdi', 15)->nullable()->comment('G03, CP01...');
            $table->text('concept_summary')->nullable();

            // ── ESTADOS Y CONTROL ──
            $table->string('sat_status')->comment('Vigente, Cancelado, No Encontrado');
            $table->boolean('is_reimbursed')->default(false)->comment('¿Ya se usó en un reembolso pagado?');

            // ── ARCHIVO FÍSICO (Solo el XML) ──
            $table->string('xml_path')->nullable()->comment('Ruta del XML en storage/app/private/administration/expense-claims/xml');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_cfdis');
    }
};
