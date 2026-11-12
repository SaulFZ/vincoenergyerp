<?php

namespace App\Models\Systems\InventoryIt;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItEquipmentComponent extends Model
{
    protected $table = 'it_equipment_components';

    protected $fillable = [
        'it_equipment_id',
        'tipo',
        'marca',
        'modelo',
        'serie',
        'serie_teclado',
        'serie_mouse',
        'serie_receptor',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(ItEquipment::class, 'it_equipment_id');
    }
}
