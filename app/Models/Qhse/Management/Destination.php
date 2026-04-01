<?php

namespace App\Models\Qhse\Management;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Destination extends Model
{
    protected $table = 'destinations';
    protected $fillable = ['name', 'parent_id', 'level'];

    // Un estado tiene muchos municipios
    public function children(): HasMany
    {
        return $this->hasMany(Destination::class, 'parent_id');
    }

    // Un municipio pertenece a un estado
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Destination::class, 'parent_id');
    }
}
