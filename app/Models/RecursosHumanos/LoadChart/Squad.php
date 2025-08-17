<?php

namespace App\Models\RecursosHumanos\LoadChart;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;

class Squad extends Model
{
    use HasFactory;

    protected $fillable = [
        'squad_number',
        'squad_name',
        'employee_id'
    ];

    /**
     * Get the employee that belongs to this squad.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Scope para obtener cuadrillas por número.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $squadNumber
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBySquadNumber($query, $squadNumber)
    {
        return $query->where('squad_number', $squadNumber);
    }

    /**
     * Obtener todas las cuadrillas agrupadas.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getAllSquadsGrouped()
    {
        return self::with('employee')
            ->orderBy('squad_number')
            ->get()
            ->groupBy('squad_number');
    }
}
