<?php

namespace App\Observers;

use App\Enums\TipoNotificacion;
use App\Models\EsInvitadoA;
use App\Services\Interfaces\NotificacionServiceInterface;

//Se dispara la notificacion cuando una persona marca o desmarca su confirmacion de asistencia a un evento
//Avisa al creador y a todos los que confirmaron presencia
class EsInvitadoAObserver
{
    public function __construct(
        protected NotificacionServiceInterface $notificacionService
    ) {}

    public function updated(EsInvitadoA $invitacion): void
    {
        //Sólo nos interesa si cambió el campo `confirmacion`
        if (! $invitacion->wasChanged('confirmacion')) {
            return;
        }

        $evento         = $invitacion->evento()->with('profesionalCreador.persona')->first();
        $invitado       = $invitacion->profesional()->with('persona')->first();
        $invitadoId     = $invitacion->fk_id_profesional;
        $invitadoNombre = $invitado?->persona?->nombre ?? 'Un profesional';

        if ($invitacion->confirmacion) {
            $tipo    = TipoNotificacion::CONFIRMACION_ASISTENCIA;
            $mensaje = "{$invitadoNombre} confirmó su asistencia al evento.";
        } else {
            $tipo    = TipoNotificacion::CANCELACION_ASISTENCIA;
            $mensaje = "{$invitadoNombre} canceló su asistencia al evento.";
        }

        //Destinatarios: creador del evento y todos los invitados que confirmaron presencia,
        //excluyendo al profesional que realizó el cambio
        $creadorId = $evento?->fk_id_profesional_creador;

        $idsConfirmados = $evento->esInvitadoA()
            ->where('confirmacion', true)
            ->where('fk_id_profesional', '!=', $invitadoId)
            ->pluck('fk_id_profesional')
            ->toArray();

        $destinatarios = collect($idsConfirmados);

        //Agregar al creador si no está ya en la lista y no es quien hizo el cambio
        if ($creadorId && $creadorId !== $invitadoId) {
            $destinatarios->push($creadorId);
        }

        foreach ($destinatarios->unique() as $destinatarioId) {
            $this->notificacionService->crear(
                tipo:           $tipo,
                mensaje:        $mensaje,
                destinatarioId: $destinatarioId,
                origenId:       $invitadoId,
                eventoId:       $invitacion->fk_id_evento,
            );
        }
    }
}
