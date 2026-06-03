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
            // Para saber con qué credencial (nodo) se descargó esta factura
            $table->foreignId('fsl_node_id')->nullable()->constrained('fsl_nodes')->nullOnDelete();

            // ── DATOS FISCALES DEL SAT ──
            $table->uuid('uuid')->unique()->comment('Folio Fiscal del SAT');
            $table->string('issuer_rfc', 13)->index()->comment('RFC del proveedor/empleado');
            $table->string('issuer_name')->nullable()->comment('Razón social del emisor');
            $table->string('receiver_rfc', 13)->index()->comment('RFC de Vinco Energy');

            // ── MONTOS Y FECHAS ──
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->string('currency', 3)->default('MXN')->comment('Moneda (MXN, USD, etc.)');
            $table->dateTime('issue_date')->comment('Fecha y hora de emisión del comprobante');

            // ── ESTADOS Y CONTROL ──
            $table->enum('sat_status', ['Vigente', 'Cancelado', 'No Encontrado'])->default('Vigente');
            $table->boolean('is_reimbursed')->default(false)->comment('¿Ya se usó en un reembolso pagado?');

            // ── ARCHIVOS FÍSICOS (En la bóveda private) ──
            $table->string('xml_path')->nullable()->comment('Ruta del XML en storage/private');
            $table->string('pdf_path')->nullable()->comment('Ruta del PDF en storage/private');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_cfdis');
    }
};
