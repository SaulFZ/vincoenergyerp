<?php

namespace App\Models\RH\OrgManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Employee;

class Area extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'responsible_id', 'is_active'];

   // El Gerente / Responsable directo del Área
    public function responsible()
    {
        return $this->belongsTo(Employee::class, 'responsible_id');
    }

    // El Área "Padre" o Superior (Ej. Operaciones es padre de Geociencias)
    public function parentArea()
    {
        return $this->belongsTo(Area::class, 'parent_id');
    }
}
