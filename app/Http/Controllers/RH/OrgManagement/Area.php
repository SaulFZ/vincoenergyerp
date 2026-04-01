<?php

namespace App\Models\RH\OrgManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Area extends Model
{
    use SoftDeletes;

    protected $table = 'areas';
    protected $fillable = ['name', 'description', 'responsible_id'];

    // Relación con el responsable (Gerente de Área)
    public function responsible()
    {
        return $this->belongsTo(Employee::class, 'responsible_id');
    }

    // Relación con sus departamentos
    public function departments()
    {
        return $this->hasMany(Department::class, 'area_id');
    }
}
