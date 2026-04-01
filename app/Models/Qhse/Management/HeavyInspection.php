<?php

namespace App\Models\Qhse\Management;

use Illuminate\Database\Eloquent\Model;

class HeavyInspection extends Model
{
    protected $table = 'heavy_inspections';

    protected $fillable = [
        'journey_unit_id',
        'fuel_level',
        'mileage',
        'doc_registration_card',
        'doc_insurance_policy',
        'doc_cargo_permit',
        'doc_emissions_cert',
        'doc_mechanical_cert',
        'doc_waybill',
        'doc_emergency_phones',
        'doc_driving_license',
        'vis_first_aid_kit',
        'vis_safety_cones',
        'vis_fire_extinguisher',
        'vis_jack',
        'vis_jumper_cables',
        'vis_flashlight',
        'vis_mirrors',
        'vis_spare_tire',
        'vis_tires_condition',
        'vis_tires_calibrated',
        'vis_doors_windows',
        'vis_body_dents',
        'vis_windshield_wipers',
        'vis_air_conditioning',
        'vis_springs_suspension',
        'vis_air_bags_suspension',
        'vis_general_lights',
        'vis_horn',
        'vis_reverse_alarm',
        'vis_logos',
        'vis_seats',
        'vis_seatbelts',
        'vis_beacon_light',
        'maint_date_km_check',
        'maint_engine_start',
        'maint_oil_pressure',
        'maint_engine_temp',
        'maint_air_pressure',
        'maint_fan_clutch',
        'maint_batteries',
        'maint_speedometer',
        'maint_rpm_indicator',
        'maint_oil_level',
        'maint_coolant_level',
        'maint_hydraulic_level',
        'maint_diesel_level',
        'maint_engine_brake',
        'maint_parking_brake',
        'maint_belts',
        'maint_air_tank_purge',
        'has_anomalies',
        'anomaly_comments',
        'photo_evidence',
    ];

    // Limpiamos los casts.
    protected $casts = [
        'has_anomalies' => 'boolean',
        'photo_evidence' => 'array',
        'mileage' => 'integer',
    ];

    public function journeyUnit()
    {
        return $this->belongsTo(JourneyUnit::class);
    }
}
