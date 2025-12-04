<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Profesional;
use Illuminate\Support\Collection;

class DailySummaryMail extends Mailable
{
    use Queueable, SerializesModels;

    public Profesional $profesional;
    public Collection $eventosHoy;
    public ?string $passwordToken;

    public function __construct(Profesional $profesional, ?string $passwordToken, Collection $eventosHoy)
    {
        $this->profesional = $profesional;
        $this->passwordToken = $passwordToken;
        $this->eventosHoy = $eventosHoy;
    }

    /**
     * Asunto del email.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'SIGPAE - Resumen de Eventos Programados para Hoy',
        );
    }

    /**
     * Contenido y vista que se enviará.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.daily-summary',
            with: [
                'profesional' => $this->profesional,
                'eventosHoy' => $this->eventosHoy
                // No se envía el token a la vista, como querías
            ]
        );
    }

    /**
     * Si algún día querés adjuntos.
     */
    public function attachments(): array
    {
        return [];
    }
}
