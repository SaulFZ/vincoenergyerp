<?php

namespace App\Models\Qhse\Gerenciamiento;

use Illuminate\Database\Eloquent\Model;

class LightInspection extends Model
{
    protected $table = 'light_inspections';

    protected $fillable = [
        'journey_unit_id',
        'fuel_level',
        'mileage',
        'doc_registration_card',
        'doc_insurance_policy',
        'doc_emergency_phones',
        'doc_driving_license',
        'vis_first_aid_kit',
        'vis_safety_triangles',
        'vis_fire_extinguisher',
        'vis_jack_wrench',
        'vis_jumper_cables',
        'vis_basic_tools',
        'vis_flashlight',
        'vis_mirrors',
        'vis_spare_tire',
        'vis_tires_condition',
        'vis_paint_condition',
        'vis_windshield_wipers',
        'vis_bumpers',
        'vis_main_lights',
        'vis_stop_reverse_lights',
        'vis_horn',
        'vis_company_logos',
        'vis_seats_condition',
        'vis_dashboard_panel',
        'vis_seatbelts',
        'maint_last_check_verified',
        'maint_leaks_check',
        'maint_fluid_levels',
        'maint_belts_condition',
        'has_anomalies',
        'anomaly_comments',
        'photo_evidence',
    ];

    // Limpiamos los casts. Ahora Laravel respetará el texto 'si', 'no', 'na'
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
