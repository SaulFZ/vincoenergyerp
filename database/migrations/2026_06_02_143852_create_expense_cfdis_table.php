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

            // ── DATOS FISCALES DEL SAT ──
            $table->uuid('uuid')->unique()->comment('Folio Fiscal del SAT');
            $table->string('issuer_rfc', 25)->index()->comment('RFC del proveedor/empleado');
            $table->string('issuer_name')->nullable()->comment('Razón social del emisor');
            $table->string('receiver_rfc', 15)->index()->comment('RFC receptor');

            // ── MONTOS Y FECHAS ──
            $table->decimal('subtotal', 20, 2);
            $table->decimal('total', 20, 2);
            $table->string('currency', 3)->comment('Moneda (MXN, USD, etc.)');
            $table->dateTime('issue_date')->comment('Fecha y hora de emisión del comprobante');

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
