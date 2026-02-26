<?php

namespace App\Observers;

use App\Enums\TipoNotificacion;
use App\Models\Intervencion;
use App\Services\Interfaces\NotificacionServiceInterface;

//Notifica al generador y a los profesionales participantes cuando
//una Intervención es editada o eliminada.
class IntervencionObserver
{
    public function __construct(
        protected NotificacionServiceInterface $notificacionService
    ) {}

    public function updated(Intervencion $intervencion): void
    {
        $editorId     = auth()->check() ? auth()->user()->getAuthIdentifier() : null;
        $editorNombre = auth()->user()?->persona?->nombre ?? 'Un profesional';

        $destinatarios = $this->obtenerDestinatarios($intervencion, $editorId);

        foreach ($destinatarios as $destinatarioId) {
            $this->notificacionService->crear(
                tipo:           TipoNotificacion::INTERVENCION_EDITADA,
                mensaje:        "La intervención del {$intervencion->fecha_hora_intervencion?->format('d/m/Y')} fue modificada por {$editorNombre}.",
                destinatarioId: $destinatarioId,
                origenId:       $editorId,
                intervencionId: $intervencion->id_intervencion,
            );
        }
    }

   
    public function deleting(Intervencion $intervencion): void
    {
        $editorId     = auth()->check() ? auth()->user()->getAuthIdentifier() : null;
        $editorNombre = auth()->user()?->persona?->nombre ?? 'Un profesional';

        $destinatarios = $this->obtenerDestinatarios($intervencion, $editorId);

        foreach ($destinatarios as $destinatarioId) {
            $this->notificacionService->crear(
                tipo:           TipoNotificacion::INTERVENCION_BORRADA,
                mensaje:        "La intervención del {$intervencion->fecha_hora_intervencion?->format('d/m/Y')} fue eliminada por {$editorNombre}.",
                destinatarioId: $destinatarioId,
                origenId:       $editorId,
                intervencionId: null, // el recurso ya no existirá
            );
        }
    }


    private function obtenerDestinatarios(Intervencion $intervencion, ?int $excluirId): array
    {
        $intervencion->loadMissing('profesionales');

        $ids = $intervencion->profesionales
            ->pluck('id_profesional')
            ->toArray();

        if ($intervencion->fk_id_profesional_generador) {
            $ids[] = $intervencion->fk_id_profesional_generador;
        }

        return array_values(array_unique(
            array_filter($ids, fn($id) => $id !== $excluirId)
        ));
    }
}
