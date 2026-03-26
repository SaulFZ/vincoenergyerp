<?php

namespace App\Models\RH\LoadChart;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FortnightlyConfig extends Model
{
    use HasFactory;

    protected $table = 'fortnightly_configs';

    protected $fillable = [
        'year',
        'month',
        'q1_start',
        'q1_end',
        'q2_start',
        'q2_end'
    ];

    protected $casts = [
        'q1_start' => 'date',
        'q1_end' => 'date',
        'q2_start' => 'date',
        'q2_end' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get config for specific month/year
     */
    public static function getForMonth($year, $month)
    {
        return static::where('year', $year)
            ->where('month', $month)
            ->first();
    }

    /**
     * Check if a date belongs to first fortnight
     */
    public function isFirstFortnight($date)
    {
        $checkDate = is_string($date) ? \Carbon\Carbon::parse($date) : $date;
        return $checkDate >= $this->q1_start && $checkDate <= $this->q1_end;
    }

    /**
     * Check if a date belongs to second fortnight
     */
    public function isSecondFortnight($date)
    {
        $checkDate = is_string($date) ? \Carbon\Carbon::parse($date) : $date;
        return $checkDate >= $this->q2_start && $checkDate <= $this->q2_end;
    }

    /**
     * Check if a date is a working day (belongs to any fortnight)
     */
    public function isWorkingDay($date)
    {
        return $this->isFirstFortnight($date) || $this->isSecondFortnight($date);
    }

    /**
     * Get all working days for the month
     */
    public function getWorkingDays()
    {
        $workingDays = [];

        // Add first fortnight days
        for ($date = $this->q1_start->copy(); $date <= $this->q1_end; $date->addDay()) {
            $workingDays[] = $date->copy();
        }

        // Add second fortnight days
        for ($date = $this->q2_start->copy(); $date <= $this->q2_end; $date->addDay()) {
            $workingDays[] = $date->copy();
        }

        return collect($workingDays)->sort();
    }

    /**
     * Get days count for first fortnight
     */
    public function getFirstFortnightDaysCount()
    {
        return $this->q1_start->diffInDays($this->q1_end) + 1;
    }

    /**
     * Get days count for second fortnight
     */
    public function getSecondFortnightDaysCount()
    {
        return $this->q2_start->diffInDays($this->q2_end) + 1;
    }
}
