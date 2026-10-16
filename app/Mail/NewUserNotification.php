<?php

namespace App\Mail;

use App\Models\UsersAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class NewUserNotification extends Mailable
{
    use Queueable, SerializesModels;

    public UsersAccount $user;

    public ?string $temporaryPassword;

    public string $verificationUrl;

    public function __construct(
        UsersAccount $user,
        ?string $temporaryPassword = null
    ) {
        $this->user = $user;

        $this->temporaryPassword =
            $temporaryPassword;

        /*
        |--------------------------------------------------------------------------
        | Generate Verification URL
        |--------------------------------------------------------------------------
        */

        $this->verificationUrl =
            URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                [
                    'id' =>
                        $user->id,

                    'hash' =>
                        sha1(
                            $user->getEmailForVerification()
                        ),
                ]
            );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject:
                'Welcome to ' .
                config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown:
                'emails.NewUserNotification',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}