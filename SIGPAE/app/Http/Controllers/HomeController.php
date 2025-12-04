<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Profesional;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index()
    {
        // Profesonal logueado (tu sistema autentica Profesionales)
        /** @var Profesional $profesional */
        $profesional = auth()->user();

        if (!$profesional) {
            abort(403, 'No autenticado.');
        }

        // -----------------------------------------------
        // 1) Eventos de HOY
        // -----------------------------------------------
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

        // -----------------------------------------------
        // 2) Eventos PRÓXIMOS (próximos 7 días)
        // -----------------------------------------------
        $ahora = Carbon::now();

        $eventosCreadosProximos = $profesional->eventosCreados()
            ->where('fecha_hora', '>', $ahora)
            ->orderBy('fecha_hora')
            ->take(7)
            ->get();

        $eventosInvitadoProximos = $profesional->eventosInvitado()
            ->where('fecha_hora', '>', $ahora)
            ->orderBy('fecha_hora')
            ->take(7)
            ->get();

        $eventosProximos = $eventosCreadosProximos
            ->merge($eventosInvitadoProximos)
            ->sortBy('fecha_hora')
            ->values();

        // -----------------------------------------------
        // 3) Retornar vista
        // -----------------------------------------------
        return view('welcome', [
    'profesional'             => $profesional,
    'eventosHoy'              => $eventosHoy,
    'eventosProximos'         => $eventosProximos,
    'eventosCreadosProximos'  => $eventosCreadosProximos,
    'eventosInvitadoProximos' => $eventosInvitadoProximos
]);

    }
}
