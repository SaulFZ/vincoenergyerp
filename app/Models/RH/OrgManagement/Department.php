<?php

namespace App\Models\RH\OrgManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Employee;

class Department extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'area_id', 'responsible_id', 'is_active'];

    // Área a la que pertenece el departamento
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    // Empleados dentro de este departamento
    public function employees()
    {
        return $this->hasMany(Employee::class, 'department_id');
    }

    // El Jefe de departamento
    public function responsible()
    {
        return $this->belongsTo(Employee::class, 'responsible_id');
    }
}
