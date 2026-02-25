<?php

namespace App\Models\Qhse\Gerenciamiento;

use Illuminate\Database\Eloquent\Model;

class RiskAssessment extends Model
{
    protected $table = 'risk_assessments';

    protected $fillable = [
        'journey_id',
        'defensive_driving_option',
        'defensive_driving_score',
        'awake_hours_option',
        'awake_hours_score',
        'fleet_composition_option',
        'fleet_composition_score',
        'communication_option',
        'communication_score',
        'weather_option',
        'weather_score',
        'lighting_option',
        'lighting_score',
        'road_condition_option',
        'road_condition_score',
        'extra_road_hazards_option',
        'extra_road_hazards_score',
        'wildlife_activity_option',
        'wildlife_activity_score',
        'route_security_option',
        'route_security_score',
        'hazardous_material_option',
        'hazardous_material_score',
        'is_night_shift',
        'has_low_sleep',
        'exceeds_midnight',
        'extreme_fatigue',
        'total_score',
        'risk_level',
    ];

    protected $casts = [
        'is_night_shift' => 'boolean',
        'has_low_sleep' => 'boolean',
        'exceeds_midnight' => 'boolean',
        'extreme_fatigue' => 'boolean',
        'defensive_driving_score' => 'integer',
        'awake_hours_score' => 'integer',
        'fleet_composition_score' => 'integer',
        'communication_score' => 'integer',
        'weather_score' => 'integer',
        'lighting_score' => 'integer',
        'road_condition_score' => 'integer',
        'extra_road_hazards_score' => 'integer',
        'wildlife_activity_score' => 'integer',
        'route_security_score' => 'integer',
        'hazardous_material_score' => 'integer',
        'total_score' => 'integer',
    ];

    public function journey()
    {
        return $this->belongsTo(Journey::class);
    }
}
