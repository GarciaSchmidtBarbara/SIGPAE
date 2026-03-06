<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Interfaces\EventoServiceInterface;
use App\Services\Interfaces\NotificacionServiceInterface;

class HomeController extends Controller
{
    public function __construct(
        protected EventoServiceInterface $eventoService,
        protected NotificacionServiceInterface $notificacionService
    ) {}

    public function index()
    {
        $profesional = auth()->user();

        if (!$profesional) {
            abort(403, 'No autenticado.');
        }

        $eventosHoy = $this->eventoService->obtenerEventosDelDia($profesional->id_profesional);
        $eventosProximos = $this->eventoService->obtenerProximosEventos($profesional->id_profesional);

        $notificaciones = $this->notificacionService->listarParaAuth()->take(10);
        $noLeidas       = $this->notificacionService->contarNoLeidas();

        return view('welcome', [
            'profesional'     => $profesional,
            'eventosHoy'      => $eventosHoy,
            'eventosProximos' => $eventosProximos,
            'notificaciones'  => $notificaciones,
            'noLeidas'        => $noLeidas,
        ]);
    }
}
