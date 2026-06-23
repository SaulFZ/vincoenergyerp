<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_claim_logs', function (Blueprint $table) {
            $table->id();

            // ── RELACIÓN CON EL REEMBOLSO PADRE ──
            $table->foreignId('expense_claim_id')->constrained('expense_claims')->cascadeOnDelete();

            // ── AUDITORÍA: ¿QUIÉN LO HIZO? ──
            $table->foreignId('user_id')->constrained('users')->comment('El usuario exacto que hizo clic en Validar/Aprobar/Rechazar o Emitir');

            // ── TRAZABILIDAD DEL MOVIMIENTO ──
            $table->string('action')->comment('Ej: Creación, Validación, Aprobación, Rechazo, Corrección');
            $table->string('previous_status')->nullable()->comment('Estado antes del clic');
            $table->string('new_status')->comment('Estado después del clic');

            // ── COMENTARIOS Y JUSTIFICACIONES ──
            $table->text('comments')->nullable()->comment('Obligatorio si es rechazo, opcional para los demás');

            // created_at nos dará la FECHA Y HORA exacta (timestamp) del movimiento
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_claim_logs');
    }
};
