<?php

namespace App\Models\Systems\Tickets;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'assigned_to', // ¡CRÍTICO! Faltaba este para la auto-asignación de TI
        'folio',
        'department_code',
        'subject',
        'description',
        'priority',
        'status',
        'email_message_id',
    ];

    /** RELACIONES */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id'); // Solicitante
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to'); // Técnico TI
    }

    public function trackings(): HasMany
    {
        return $this->hasMany(TicketTracking::class)->orderBy('created_at', 'asc');
    }

    /** ACCESSORES */
    protected function displayId(): Attribute
    {
        return Attribute::make(
            get: fn () => 'TK-' . str_pad($this->id, 3, '0', STR_PAD_LEFT),
        );
    }
}
