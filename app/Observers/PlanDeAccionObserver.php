<?php

namespace App\Observers;

use App\Enums\TipoNotificacion;
use App\Models\PlanDeAccion;
use App\Services\Interfaces\NotificacionServiceInterface;

//Notifica al generador y a los participantes cuando un Plan de Acción es editado o eliminado.

class PlanDeAccionObserver
{
    public function __construct(
        protected NotificacionServiceInterface $notificacionService
    ) {}

    public function updated(PlanDeAccion $plan): void
    {
        $editorId     = auth()->check() ? auth()->user()->getAuthIdentifier() : null;
        $editorNombre = auth()->user()?->persona?->nombre ?? 'Un profesional';

        $destinatarios = $this->obtenerDestinatarios($plan, $editorId);

        foreach ($destinatarios as $destinatarioId) {
            $this->notificacionService->crear(
                tipo:           TipoNotificacion::PLAN_EDITADO,
                mensaje:        "El Plan de Acción #{$plan->id_plan_de_accion} fue modificado por {$editorNombre}.",
                destinatarioId: $destinatarioId,
                origenId:       $editorId,
                planId:         $plan->id_plan_de_accion,
            );
        }
    }


    public function deleting(PlanDeAccion $plan): void
    {
        $editorId     = auth()->check() ? auth()->user()->getAuthIdentifier() : null;
        $editorNombre = auth()->user()?->persona?->nombre ?? 'Un profesional';

        $destinatarios = $this->obtenerDestinatarios($plan, $editorId);

        foreach ($destinatarios as $destinatarioId) {
            $this->notificacionService->crear(
                tipo:           TipoNotificacion::PLAN_BORRADO,
                mensaje:        "El Plan de Acción #{$plan->id_plan_de_accion} fue eliminado por {$editorNombre}.",
                destinatarioId: $destinatarioId,
                origenId:       $editorId,
                planId:         null, // el recurso ya no existirá
            );
        }
    }

  
    private function obtenerDestinatarios(PlanDeAccion $plan, ?int $excluirId): array
    {
        $plan->loadMissing(['profesionalesParticipantes', 'profesionalGenerador']);

        $ids = $plan->profesionalesParticipantes
            ->pluck('id_profesional')
            ->toArray();

        if ($plan->fk_id_profesional_generador) {
            $ids[] = $plan->fk_id_profesional_generador;
        }

        return array_values(array_unique(
            array_filter($ids, fn($id) => $id !== $excluirId)
        ));
    }
}
