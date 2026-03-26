<?php

namespace App\Models\RH\LoadChart;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoadChartAssignment extends Model
{
    use HasFactory;

    protected $table = 'load_chart_assignments';

    protected $fillable = [
        'employee_id',
        'reviewer_id',
        'approver_id'
    ];

    protected $casts = [
        'reviewer_id' => 'integer',
        'approver_id' => 'integer',
        'employee_id' => 'integer'
    ];

    /**
     * Relación con el empleado asignado
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Relación con el usuario revisor
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * Relación con el usuario aprobador
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * Buscar asignación por empleado
     */
    public static function findByEmployee($employeeId)
    {
        return self::where('employee_id', $employeeId)->first();
    }

    /**
     * Actualizar o crear una asignación
     */
    public static function updateOrCreateAssignment($employeeId, $reviewerId, $approverId)
    {
        return self::updateOrCreate(
            ['employee_id' => $employeeId],
            [
                'reviewer_id' => $reviewerId,
                'approver_id' => $approverId
            ]
        );
    }
}
