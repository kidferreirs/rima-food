<?php

namespace App\Mail;

use App\Models\Restaurante;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeRimaFoodMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public Restaurante $restaurante
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🚀 Bem-vindo ao Rima Food!',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.welcome-rima-food',
            with: [
                'user' => $this->user,
                'restaurante' => $this->restaurante,
            ],
        );
    }

    public function attachments(): array
    {
        $manual = storage_path(
            'app/manuals/Manual_Oficial_Rima_Food_v2.pdf'
        );

        if (! file_exists($manual)) {
            return [];
        }

        return [
            Attachment::fromPath($manual)
                ->as('Manual_Rima_Food.pdf')
                ->withMime('application/pdf'),
        ];
    }
}