<?php

namespace App\Models\Systems\Tickets;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketTracking extends Model
{
    protected $table = 'ticket_trackings';

    protected $fillable = [
        'ticket_id',
        'user_id',
        'message',
        'status_after',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
