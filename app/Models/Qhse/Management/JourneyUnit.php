<?php

namespace App\Models\Qhse\Management;

use Illuminate\Database\Eloquent\Model;

class JourneyUnit extends Model
{
    protected $table = 'journey_units';

    protected $fillable = [
        'journey_id',
        'unit_type',
        'economic_number',
        'driver_name',
        'driver_id',
        'alcohol_pct',
        'blood_pressure',
        'takes_medication',
        'medication_name',
        'state_license_validity',
        'light_defensive_driving_validity',
        'federal_license_validity',
        'heavy_defensive_driving_validity',
        'sleep_at',
        'wake_up_at',
        'total_sleep_hours',
        'awake_hours_before',
        'journey_duration',
        'total_active_hours',
        'passengers',
    ];

    protected $casts = [
        'alcohol_pct' => 'decimal:2',
        'sleep_at' => 'datetime:H:i',
        'wake_up_at' => 'datetime:H:i',
        'passengers' => 'array',
    ];

    public function journey()
    {
        return $this->belongsTo(Journey::class);
    }

    public function lightInspection()
    {
        return $this->hasOne(LightInspection::class, 'journey_unit_id');
    }

    public function heavyInspection()
    {
        return $this->hasOne(HeavyInspection::class, 'journey_unit_id');
    }
}
