<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ActivarCuentaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $url;
    public $profesional;
    /**
     * Create a new message instance.
     */
    public function __construct($url, $profesional)
    {
        $this->url = $url;
        $this->profesional = $profesional;
    }

    public function build(){
        return $this->markdown('emails.activar-cuenta')
            ->subject('activar cuenta - SIGPAE')
            ->with([
                'url' => $this->url,
                'profesional' => $this->profesional,
            ]);
    }
}
