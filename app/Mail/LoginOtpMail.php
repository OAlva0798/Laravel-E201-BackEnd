<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LoginOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otp,
        public string $systemName = 'System Portal',
    ) {
    }

    public function build()
    {
        return $this
            ->subject("{$this->systemName} - Login Verification Code")
            ->view('emails.login-otp');
    }
}