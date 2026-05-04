<?php

namespace App\Mail\RH\LoadChart;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Employee;
use App\Models\RH\OrgManagement\Area;
use Carbon\Carbon;

class CommissionNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $employee;
    public $area;
    public $date;
    public $activityType;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Employee $employee, ?Area $area, $date, $activityType)
    {
        $this->employee = $employee;
        $this->area = $area;
        $this->date = $date;
        $this->activityType = $activityType;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $fechaFormat = Carbon::parse($this->date)->format('d/m/Y');

        return $this->subject('Notificación de Comisión - ' . $this->employee->full_name)
                    ->view('emails.rh.loadchart.commission_notification')
                    ->with([
                        'fechaFormat' => $fechaFormat
                    ]);
    }
}
