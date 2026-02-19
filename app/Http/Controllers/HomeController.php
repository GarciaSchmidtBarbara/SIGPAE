<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Profesional;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index()
    {
        /** @var Profesional $profesional */
        $profesional = auth()->user();

        if (!$profesional) {
            abort(403, 'No autenticado.');
        }

        $hoy = Carbon::today()->toDateString();

        $eventosCreadosHoy = $profesional->eventosCreados()
            ->whereDate('fecha_hora', $hoy)
            ->orderBy('fecha_hora')
            ->get();

        $eventosInvitadoHoy = $profesional->eventosInvitado()
            ->whereDate('fecha_hora', $hoy)
            ->orderBy('fecha_hora')
            ->get();

        $eventosHoy = $eventosCreadosHoy
            ->merge($eventosInvitadoHoy)
            ->sortBy('fecha_hora')
            ->values();

        $hoy = Carbon::today();
        $fin = Carbon::now()->addMonths(2)->endOfDay();

        // Próximos eventos: desde hoy hasta 2 meses después
        $eventosCreadosProximos = $profesional->eventosCreados()
            ->whereBetween('fecha_hora', [$hoy, $fin])
            ->orderBy('fecha_hora')
            ->get();

        $eventosInvitadoProximos = $profesional->eventosInvitado()
            ->whereBetween('fecha_hora', [$hoy, $fin])
            ->orderBy('fecha_hora')
            ->get();

        $eventosProximos = $eventosCreadosProximos
            ->merge($eventosInvitadoProximos)
            ->sortBy('fecha_hora')
            ->take(3)
            ->values();
            
        return view('welcome', [
    'profesional'             => $profesional,
    'eventosHoy'              => $eventosHoy,
    'eventosProximos'         => $eventosProximos,
    'eventosCreadosProximos'  => $eventosCreadosProximos,
    'eventosInvitadoProximos' => $eventosInvitadoProximos
]);

    }
}
