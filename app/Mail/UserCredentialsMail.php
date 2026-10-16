<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class UserCredentialsMail extends Mailable
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {}

    public function build()
    {
        return $this->subject("User account credentials")
            ->view("emails.user-credentials");
    }
}
