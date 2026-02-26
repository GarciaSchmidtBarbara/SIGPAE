<?php

namespace App\Services\Implementations;

use App\Enums\TipoNotificacion;
use App\Repositories\Interfaces\NotificacionRepositoryInterface;
use App\Services\Interfaces\NotificacionServiceInterface;
use Illuminate\Support\Collection;

class NotificacionService implements NotificacionServiceInterface
{
    public function __construct(
        protected NotificacionRepositoryInterface $repo
    ) {}

    public function crear(
        TipoNotificacion $tipo,
        string $mensaje,
        int $destinatarioId,
        ?int $origenId = null,
        ?int $eventoId = null,
        ?int $planId = null,
        ?int $intervencionId = null,
    ): void {
        $this->repo->create([
            'tipo'                           => $tipo,
            'mensaje'                        => $mensaje,
            'leida'                          => false,
            'fk_id_profesional_destinatario' => $destinatarioId,
            'fk_id_profesional_origen'       => $origenId,
            'fk_id_evento'                   => $eventoId,
            'fk_id_plan_de_accion'           => $planId,
            'fk_id_intervencion'             => $intervencionId,
        ]);
    }

    public function listarParaAuth(): Collection
    {
        $profesionalId = auth()->user()->getAuthIdentifier();

        return $this->repo->getByDestinatario($profesionalId);
    }

    public function contarNoLeidas(): int
    {
        $profesionalId = auth()->user()->getAuthIdentifier();

        return $this->repo->countNoLeidas($profesionalId);
    }

    public function marcarLeida(int $id): void
    {
        $profesionalId = auth()->user()->getAuthIdentifier();

        $this->repo->marcarLeida($id, $profesionalId);
    }

    public function marcarTodasLeidas(): void
    {
        $profesionalId = auth()->user()->getAuthIdentifier();

        $this->repo->marcarTodasLeidas($profesionalId);
    }
}
