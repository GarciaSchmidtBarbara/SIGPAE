<?php

namespace App\Observers;

use App\Enums\TipoNotificacion;
use App\Models\Evento;
use App\Services\Interfaces\NotificacionServiceInterface;

//Notifica a los invitados cuando un evento es editado o eliminado.
class EventoObserver
{
    public function __construct(
        protected NotificacionServiceInterface $notificacionService
    ) {}

    public function updated(Evento $evento): void
    {
        $editorId = auth()->check() ? auth()->user()->getAuthIdentifier() : null;

        $invitados = $evento->esInvitadoA()->with('profesional.persona')->get();

        foreach ($invitados as $invitacion) {
            //No notificar al editor de su propio cambio
            if ($invitacion->fk_id_profesional === $editorId) {
                continue;
            }

            $editorNombre = auth()->user()?->persona?->nombre ?? 'Un profesional';

            $this->notificacionService->crear(
                tipo:           TipoNotificacion::EVENTO_EDITADO,
                mensaje:        "El evento del {$evento->fecha_hora?->format('d/m/Y')} fue modificado por {$editorNombre}.",
                destinatarioId: $invitacion->fk_id_profesional,
                origenId:       $editorId,
                eventoId:       $evento->id_evento,
            );
        }
    }

    
    //Se dispara antes de la eliminación para capturar la lista de invitados.
    //No se pasa eventoId porque el registro va a desaparecer.
    public function deleting(Evento $evento): void
    {
        $editorId = auth()->check() ? auth()->user()->getAuthIdentifier() : null;
        $editorNombre = auth()->user()?->persona?->nombre ?? 'Un profesional';

        $invitados = $evento->esInvitadoA()->get();

        foreach ($invitados as $invitacion) {
            if ($invitacion->fk_id_profesional === $editorId) {
                continue;
            }

            $this->notificacionService->crear(
                tipo:           TipoNotificacion::EVENTO_BORRADO,
                mensaje:        "El evento del {$evento->fecha_hora?->format('d/m/Y')} en '{$evento->lugar}' fue eliminado por {$editorNombre}.",
                destinatarioId: $invitacion->fk_id_profesional,
                origenId:       $editorId,
                eventoId:       null, //el recurso se borra
            );
        }
    }
}
