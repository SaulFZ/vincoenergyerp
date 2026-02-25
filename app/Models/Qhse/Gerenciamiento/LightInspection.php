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

    protected $casts = [
        'doc_registration_card' => 'boolean',
        'doc_insurance_policy' => 'boolean',
        'doc_emergency_phones' => 'boolean',
        'doc_driving_license' => 'boolean',
        'vis_first_aid_kit' => 'boolean',
        'vis_safety_triangles' => 'boolean',
        'vis_fire_extinguisher' => 'boolean',
        'vis_jack_wrench' => 'boolean',
        'vis_jumper_cables' => 'boolean',
        'vis_basic_tools' => 'boolean',
        'vis_flashlight' => 'boolean',
        'vis_mirrors' => 'boolean',
        'vis_spare_tire' => 'boolean',
        'vis_tires_condition' => 'boolean',
        'vis_paint_condition' => 'boolean',
        'vis_windshield_wipers' => 'boolean',
        'vis_bumpers' => 'boolean',
        'vis_main_lights' => 'boolean',
        'vis_stop_reverse_lights' => 'boolean',
        'vis_horn' => 'boolean',
        'vis_company_logos' => 'boolean',
        'vis_seats_condition' => 'boolean',
        'vis_dashboard_panel' => 'boolean',
        'vis_seatbelts' => 'boolean',
        'maint_last_check_verified' => 'boolean',
        'maint_leaks_check' => 'boolean',
        'maint_fluid_levels' => 'boolean',
        'maint_belts_condition' => 'boolean',
        'has_anomalies' => 'boolean',
        'photo_evidence' => 'array',
        'mileage' => 'integer',
    ];

    public function journeyUnit()
    {
        return $this->belongsTo(JourneyUnit::class);
    }
}
