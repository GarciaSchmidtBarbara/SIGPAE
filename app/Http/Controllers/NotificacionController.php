<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\NotificacionServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class NotificacionController extends Controller
{
    public function __construct(
        protected NotificacionServiceInterface $notificacionService
    ) {}

    //Devuelve las notificaciones del usuario (JSON).

    public function index(): JsonResponse
    {
        $notificaciones = $this->notificacionService->listarParaAuth();

        $data = $notificaciones->map(function ($n) {
            return [
                'id'              => $n->id_notificacion,
                'tipo'            => $n->tipo->value,
                'etiqueta'        => $n->tipo->etiqueta(),
                'icono'           => $n->tipo->icono(),
                'icono_color'     => $n->tipo->iconoColor(),
                'mensaje'         => $n->mensaje,
                'leida'           => $n->leida,
                'fecha'           => $n->created_at->diffForHumans(),
                'url'             => $n->urlDestino(),
                'recurso_borrado' => $n->recursoBorrado(),
            ];
        });

        return response()->json([
            'notificaciones' => $data,
            'no_leidas'      => $this->notificacionService->contarNoLeidas(),
        ]);
    }

//Marca una notificación como leída y redirige a su destino (si existe).
    public function marcarYRedirigir(int $id): RedirectResponse
    {
        $this->notificacionService->marcarLeida($id);

        $notificaciones = $this->notificacionService->listarParaAuth();
        $notificacion   = $notificaciones->firstWhere('id_notificacion', $id);

        $url = $notificacion?->urlDestino();

        return $url
            ? redirect($url)
            : redirect()->back()->with('info', 'El recurso asociado fue eliminado.');
    }

//marcar las notificaciones como leídas sin redirigir
    public function marcarTodasLeidas(): JsonResponse
    {
        $this->notificacionService->marcarTodasLeidas();

        return response()->json(['success' => true]);
    }
}
