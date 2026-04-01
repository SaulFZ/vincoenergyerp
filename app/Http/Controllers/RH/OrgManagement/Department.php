<?php

namespace App\Models\RH\OrgManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use SoftDeletes;

    protected $table = 'departments';
    protected $fillable = ['name', 'area_id', 'responsible_id'];

    // Área a la que pertenece
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    // Responsable del departamento (Jefe de Depto)
    public function responsible()
    {
        return $this->belongsTo(Employee::class, 'responsible_id');
    }
}
