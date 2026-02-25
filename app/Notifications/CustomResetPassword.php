<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;

class CustomResetPassword extends Notification
{
    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->email,
        ], false));

        return (new MailMessage)
            ->subject('Tu enlace para restablecer contraseña')
            ->greeting('Hola!')
            ->line('Recibimos una solicitud para restablecer tu contraseña.')
            ->line('Aquí está tu token:')
            ->line($this->token)
            ->action('Restablecer contraseña', $url)
            ->line('Si no realizaste esta solicitud, ignora este correo.');
    }
}
