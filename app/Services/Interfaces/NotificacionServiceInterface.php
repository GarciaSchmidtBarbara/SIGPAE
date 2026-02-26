<?php

namespace App\Services\Interfaces;

use App\Enums\TipoNotificacion;
use Illuminate\Support\Collection;

interface NotificacionServiceInterface
{
    //Crea una notificación y la envía al profesional destinatario.
    public function crear(
        TipoNotificacion $tipo,
        string $mensaje,
        int $destinatarioId,
        ?int $origenId = null,
        ?int $eventoId = null,
        ?int $planId = null,
        ?int $intervencionId = null,
    ): void;

    //Todas las notificaciones del profesional autenticado, ordenadas de más reciente a más antigua.
    //Importante que sea de reciente a vieja
    public function listarParaAuth(): Collection;

    //Cantidad de notificaciones no leídas del profesional autenticado.
    //usado para saber que número de no leídas mostrar
    public function contarNoLeidas(): int;

    //Marca una notificación específica como leída.
    public function marcarLeida(int $id): void;

    //Marca todas las notificaciones del profesional autenticado como leídas.
    public function marcarTodasLeidas(): void;
}
