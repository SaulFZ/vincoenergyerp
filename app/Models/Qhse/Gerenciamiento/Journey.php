<?php
namespace App\Models\Qhse\Gerenciamiento;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Journey extends Model
{
    use SoftDeletes;

    protected $table = 'journeys';

    protected $fillable = [
        'folio',
        'request_date',
        'creator_name',
        'department',
        'approval_status',
        'journey_status',
        'destination_region',
        'specific_destination',
        'origin_address',
        'destination_address',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'estimated_duration',
        'has_stops',
        'planned_stops',
        'total_units',
        'fleet_type',
        'risk_score',
        'risk_level',
        'created_by',
        'approver_id',
    ];

    protected $casts = [
        'request_date'  => 'date',
        'start_date'    => 'date',
        'end_date'      => 'date',
        'has_stops'     => 'boolean',
        'planned_stops' => 'array',
        'total_units'   => 'integer',
        'risk_score'    => 'integer',
    ];

/**
 * Relación con la bitácora de eventos
 */
    public function logs()
    {
        // Traemos los logs ordenados del más antiguo al más reciente
        return $this->hasMany(JourneyLog::class, 'journey_id')->orderBy('created_at', 'asc');
    }

    public function units()
    {
        return $this->hasMany(JourneyUnit::class, 'journey_id');
    }

    public function riskAssessment()
    {
        return $this->hasOne(RiskAssessment::class, 'journey_id');
    }

    public function preConvoyMeeting()
    {
        return $this->hasOne(PreConvoyMeeting::class, 'journey_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
