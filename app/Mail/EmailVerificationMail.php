<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $activationCode;

    public function __construct($activationCode)
    {
        $this->activationCode = $activationCode;
    }

    public function build()
    {
        return $this->subject('Verify Your Email Address')
            ->view('emails.verify-email')
            ->with(['activationCode' => $this->activationCode]);
    }
}
