<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ShopOwnerAccountCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $owner,
        public readonly string $temporaryPassword,
        public readonly string $resetToken,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your shop owner account is ready',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.shop-owner-account-created',
            with: [
                'owner' => $this->owner,
                'temporaryPassword' => $this->temporaryPassword,
                'loginUrl' => $this->frontendUrl('/login'),
                'resetUrl' => $this->frontendUrl('/reset-password?token='.urlencode($this->resetToken).'&email='.urlencode($this->owner->email)),
            ],
        );
    }

    private function frontendUrl(string $path): string
    {
        $baseUrl = rtrim(config('app.frontend_url', config('app.url')), '/');

        return $baseUrl.'/'.ltrim($path, '/');
    }
}
