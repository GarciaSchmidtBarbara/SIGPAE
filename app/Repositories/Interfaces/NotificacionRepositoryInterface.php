<?php

namespace App\Repositories\Interfaces;

use App\Enums\TipoNotificacion;
use App\Models\Notificacion;
use Illuminate\Support\Collection;

interface NotificacionRepositoryInterface
{
    //Crea una nueva notificación
    public function create(array $data): Notificacion;

    //Obtiene todas las notificaciones de un profesional, ordenadas de más reciente a más antigua
    public function getByDestinatario(int $profesionalId): Collection;

    //Cuenta las notificaciones no leídas de un profesional
    public function countNoLeidas(int $profesionalId): int;

    //Marca una notificación específica como leída (solo si pertenece al profesional)
    public function marcarLeida(int $notificacionId, int $profesionalId): void;

    //Marca todas las notificaciones no leídas de un profesional como leídas
    public function marcarTodasLeidas(int $profesionalId): void;
}
