<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $token,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset your password',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset',
            with: [
                'user' => $this->user,
                'resetUrl' => $this->frontendUrl('/reset-password?token='.urlencode($this->token).'&email='.urlencode($this->user->email)),
            ],
        );
    }

    private function frontendUrl(string $path): string
    {
        $baseUrl = rtrim(config('app.frontend_url', config('app.url')), '/');

        return $baseUrl.'/'.ltrim($path, '/');
    }
}
