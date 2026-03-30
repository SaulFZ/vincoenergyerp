<?php

namespace App\Models\RH\OrgManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Employee;

class Area extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'description', 'responsible_id', 'is_active'];

    // Empleados que pertenecen a esta área
    public function employees()
    {
        return $this->hasMany(Employee::class, 'area_id');
    }

    // Departamentos que pertenecen a esta área
    public function departments()
    {
        return $this->hasMany(Department::class, 'area_id');
    }

    // El Gerente/Director a cargo del área
    public function responsible()
    {
        return $this->belongsTo(Employee::class, 'responsible_id');
    }
}
