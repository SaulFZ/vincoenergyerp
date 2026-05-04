<?php
namespace App\Models\RH\OrgManagement;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Area extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'responsible_id',
        'parent_id', // Nombre estándar
        'is_active',
    ];

    // --- RELACIONES DE JERARQUÍA ENTRE ÁREAS ---

    public function parentArea()
    {
        // Al usar 'parent_id', Laravel lo detecta casi en automático
        return $this->belongsTo(Area::class, 'parent_id');
    }

    public function childAreas()
    {
        return $this->hasMany(Area::class, 'parent_id');
    }

    // --- RELACIONES EXISTENTES ---

    public function employees()
    {
        return $this->hasMany(Employee::class, 'area_id');
    }

    public function departments()
    {
        return $this->hasMany(Department::class, 'area_id');
    }

    public function responsible()
    {
        return $this->belongsTo(Employee::class, 'responsible_id');
    }
}
