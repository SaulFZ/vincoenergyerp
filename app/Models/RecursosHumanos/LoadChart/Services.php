<?php

namespace App\Models\RecursosHumanos\LoadChart;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Services extends Model
{
    use HasFactory;

    protected $fillable = [
        'operation_type',
        'service_type',
        'service_performed',
        'identifier',
        'service_description',
        'amount',
        'currency'
    ];

    public function scopeByOperationType($query, $type)
    {
        return $query->where('operation_type', $type);
    }

    public function scopeByServiceType($query, $type)
    {
        return $query->where('service_type', $type);
    }

    public function scopeByServicePerformed($query, $performed)
    {
        return $query->where('service_performed', $performed);
    }

    public static function getGroupedServices()
    {
        return self::select('operation_type', 'service_type', 'service_performed', 'identifier', 'service_description')
            ->orderBy('operation_type')
            ->orderBy('service_type')
            ->orderBy('service_performed')
            ->get()
            ->groupBy(['operation_type', 'service_type', 'service_performed']);

    }
}
