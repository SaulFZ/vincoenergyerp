<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeMonthlyWorkLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_monthly_work_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees');
            $table->foreignId('user_id')->constrained('users');
            $table->string('month_and_year', 7);
            $table->json('daily_activities');
            /* [{"date": "2025-08-28", "is_locked": null, "day_status": "Pending", "food_bonuses": [], "activity_type": "B", "field_bonuses": [{"days": 1, "status": "Pending", "currency": "MXN", "bonus_type": "Bono de Campo Doble 2", "daily_amount": 1000, "usd_to_mxn_rate": null, "bonus_identifier": "2", "daily_amount_mxn": null, "rejection_reason": null, "daily_currency_mxn": null}], "services_list": [], "commissioned_to": null, "payroll_bonuses": [], "rejection_reason": null, "activity_description": "Trabajo en Base", "payroll_period_marker": "start_of_period_1"}, {"date": "2025-08-29", "is_locked": null, "day_status": "Pending", "food_bonuses": [], "activity_type": "P", "field_bonuses": [], "services_list": [{"amount": 25000, "status": "Pending", "currency": "MXN", "rejection_reason": null, "service_performed": "Calibración de pozo", "service_identifier": "T-1"}], "commissioned_to": null, "payroll_bonuses": [], "rejection_reason": null, "activity_description": "Trabajo en Pozo", "payroll_period_marker": null}, {"date": "2025-08-30", "is_locked": null, "day_status": "Pending", "food_bonuses": [], "activity_type": "H", "field_bonuses": [{"days": 1, "status": "Pending", "currency": "MXN", "bonus_type": "Bono de Campo Doble 2", "daily_amount": 1000, "usd_to_mxn_rate": null, "bonus_identifier": "2", "daily_amount_mxn": null, "rejection_reason": null, "daily_currency_mxn": null}], "services_list": [], "commissioned_to": null, "payroll_bonuses": [{"days": 1, "status": "Pending", "bonus_name": "Bono de nómina", "total_amount": 800, "rejection_reason": null}], "rejection_reason": null, "activity_description": "Home Office", "payroll_period_marker": null}, {"date": "2025-08-31", "is_locked": null, "day_status": "Pending", "food_bonuses": [{"status": "Pending", "currency": "MXN", "num_daily": "2", "bonus_type": "Bono de Comida", "daily_amount": 260, "rejection_reason": null}], "activity_type": "B", "field_bonuses": [{"days": 1, "status": "Pending", "currency": "USD", "bonus_type": "CBL/VDL", "daily_amount": 650, "usd_to_mxn_rate": 18.6792, "bonus_identifier": "GG-6", "daily_amount_mxn": 12141.48, "rejection_reason": null, "daily_currency_mxn": "MXN"}], "services_list": [], "commissioned_to": null, "payroll_bonuses": [{"days": 1, "status": "Pending", "bonus_name": "Bono de nómina", "total_amount": 800, "rejection_reason": null}], "rejection_reason": null, "activity_description": "Trabajo en Base", "payroll_period_marker": null}, {"date": "2025-09-01", "is_locked": null, "day_status": "Pending", "food_bonuses": [{"status": "Pending", "currency": "MXN", "num_daily": "3", "bonus_type": "Bono de Comida", "daily_amount": 380, "rejection_reason": null}], "activity_type": "P", "field_bonuses": [{"days": 1, "status": "Pending", "currency": "USD", "bonus_type": "PNN Plus", "daily_amount": 400, "usd_to_mxn_rate": 18.6792, "bonus_identifier": "GS-3", "daily_amount_mxn": 7471.68, "rejection_reason": null, "daily_currency_mxn": "MXN"}], "services_list": [{"amount": 25000, "status": "Pending", "currency": "MXN", "rejection_reason": null, "service_performed": "Calibración de pozo", "service_identifier": "T-1"}], "commissioned_to": null, "payroll_bonuses": [{"days": 3, "status": "Pending", "bonus_name": "Bono de nómina", "total_amount": 2400, "rejection_reason": null}], "rejection_reason": null, "activity_description": "Trabajo en Pozo", "payroll_period_marker": null}] */
            $table->timestamp('reviewed_at')->nullable();                       // Campo para la revisión
            $table->foreignId('reviewed_by')->nullable()->constrained('users'); // Quién revisó
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->unique(['employee_id', 'month_and_year']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_monthly_work_logs');
    }
}
