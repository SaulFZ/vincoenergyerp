<?php

namespace App\Mail\RH\LoadChart;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DayRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $employeeName;
    public $date;
    public $rejectionReason;
    public $rejectedItems;
    public $rejectedBy;

    /**
     * Create a new message instance.
     */
    public function __construct($employeeName, $date, $rejectionReason, $rejectedItems, $rejectedBy)
    {
        $this->employeeName = $employeeName;
        $this->date = $date;
        $this->rejectionReason = $rejectionReason;
        $this->rejectedItems = $rejectedItems;
        $this->rejectedBy = $rejectedBy;
    }

    /**
     * Get the message envelope.
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Elementos Rechazados - ' . $this->date . ' - Sistema LoadChart',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content()
    {
        return new Content(
            view: 'emails.rh.loadchart.day_rejected',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments()
    {
        return [];
    }
}
