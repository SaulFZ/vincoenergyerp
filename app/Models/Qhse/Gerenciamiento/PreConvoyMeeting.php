<?php

namespace App\Models\Qhse\Gerenciamiento;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\User;

class PreConvoyMeeting extends Model
{
    protected $table = 'pre_convoy_meetings';

    protected $fillable = [
        'journey_id',
        'convoy_leader_id',
        'understand_stopping_points',
        'know_convoy_break_protocol',
        'documents_verified',
        'accident_prevention_aware',
        'has_emergency_contacts',
        'leader_commitment_confirmed',
    ];

    protected $casts = [
        'understand_stopping_points' => 'boolean',
        'know_convoy_break_protocol' => 'boolean',
        'documents_verified' => 'boolean',
        'accident_prevention_aware' => 'boolean',
        'has_emergency_contacts' => 'boolean',
        'leader_commitment_confirmed' => 'boolean',
    ];

    public function journey()
    {
        return $this->belongsTo(Journey::class);
    }

    public function leader()
    {
        return $this->belongsTo(User::class, 'convoy_leader_id');
    }
}
