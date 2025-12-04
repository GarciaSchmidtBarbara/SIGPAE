<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Mail;
use App\Models\Profesional;
use App\Mail\DailySummaryMail;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// scheduler para envio diario de correos
Schedule::call(function () {
    $horaActual = Carbon::now()->format('H:i');

    $profesionales = Profesional::where('hora_envio_resumen_diario', $horaActual)
        ->whereNotNull('email')
        ->get();
    
    foreach($profesionales as $profesional) {
        $fechaHoy = now()->toDateString();

        $eventosCreadosHoy = $profesional->eventosCreados()
            ->whereDate('fecha_hora', $fechaHoy)
            ->get();

        $eventosInvitadoHoy = $profesional->eventosInvitado()
            ->whereDate('fecha_hora', $fechaHoy)
            ->get();
        
        $eventosHoy = $eventosCreadosHoy->merge($eventosInvitadoHoy)
            ->sortBy('fecha_hora');
        
        Mail::to($profesional->email)->send(
            new DailySummaryMail($profesional, null, $eventosHoy) 
        );
        
        \Log::info("Correo de resumen enviado a {$profesional->email} a la hora solicitada.");
    } 
})->name('envio-resumen-profesional')
  ->everyMinute() 
  ->onSuccess(fn () => \Log::info('Revision de correo diario completada.'))
  ->onFailure(fn () => \Log::error('Fallo en la revisión de correo diario.'))
  ->withoutOverlapping();