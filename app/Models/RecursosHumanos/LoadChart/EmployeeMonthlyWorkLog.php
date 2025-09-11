<?php

namespace App\Models\RecursosHumanos\LoadChart;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;
use App\Models\Auth\User;

class EmployeeMonthlyWorkLog extends Model
{
    use HasFactory;

    protected $table = 'employee_monthly_work_logs';

    protected $fillable = [
        'employee_id',
        'user_id',
        'month_and_year',
        'daily_activities',
        'reviewed_at',
        'reviewed_by',
        'approved_at',
        'approved_by'
    ];

    protected $casts = [
        'daily_activities' => 'array',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relaciones
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Métodos auxiliares
    public function addDailyActivity($date, $activityData)
    {
        $activities = $this->daily_activities ?? [];

        // Buscar si ya existe una actividad para esta fecha
        $existingIndex = null;
        foreach ($activities as $index => $activity) {
            if ($activity['date'] === $date) {
                $existingIndex = $index;
                break;
            }
        }

        if ($existingIndex !== null) {
            // Actualizar actividad existente
            $activities[$existingIndex] = $activityData;
        } else {
            // Agregar nueva actividad
            $activities[] = $activityData;
        }

        // Ordenar por fecha
        usort($activities, function ($a, $b) {
            return strcmp($a['date'], $b['date']);
        });

        $this->daily_activities = $activities;
        return $this;
    }

    public function getDailyActivity($date)
    {
        $activities = $this->daily_activities ?? [];

        foreach ($activities as $activity) {
            if ($activity['date'] === $date) {
                return $activity;
            }
        }

        return null;
    }

    public function removeDailyActivity($date)
    {
        $activities = $this->daily_activities ?? [];
        $activities = array_filter($activities, function ($activity) use ($date) {
            return $activity['date'] !== $date;
        });

        $this->daily_activities = array_values($activities);
        return $this;
    }

    public function getActivitiesForDateRange($startDate, $endDate)
    {
        $activities = $this->daily_activities ?? [];
        return array_filter($activities, function ($activity) use ($startDate, $endDate) {
            return $activity['date'] >= $startDate && $activity['date'] <= $endDate;
        });
    }

    public function isLocked()
    {
        return !is_null($this->approved_at);
    }

    public function isReviewed()
    {
        return !is_null($this->reviewed_at);
    }

    // Scope para filtrar por mes y año
    public function scopeForMonthYear($query, $month, $year)
    {
        $monthYear = sprintf('%04d-%02d', $year, $month);
        return $query->where('month_and_year', $monthYear);
    }

    // Scope para filtrar por empleado
    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }
}
