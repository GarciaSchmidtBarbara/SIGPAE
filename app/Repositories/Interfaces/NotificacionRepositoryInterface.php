<?php

namespace App\Repositories\Interfaces;

use App\Enums\TipoNotificacion;
use App\Models\Notificacion;
use Illuminate\Support\Collection;

interface NotificacionRepositoryInterface
{
    public function create(array $data): Notificacion;
    public function getByDestinatario(int $profesionalId): Collection;
    public function countNoLeidas(int $profesionalId): int;
    public function marcarLeida(int $notificacionId, int $profesionalId): void;
    public function marcarTodasLeidas(int $profesionalId): void;
}
