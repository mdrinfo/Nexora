<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class DailyReport extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $filePath;

    public function __construct($data, $filePath)
    {
        $this->data = $data;
        $this->filePath = $filePath;
    }

    public function build()
    {
        return $this->subject('Nexora: Rapport Journalier - ' . $this->data['date'])
                    ->view('emails.daily_report')
                    ->attach(Storage::path($this->filePath));
    }
}
