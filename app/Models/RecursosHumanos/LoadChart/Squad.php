<?php

namespace App\Models\RecursosHumanos\LoadChart;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
