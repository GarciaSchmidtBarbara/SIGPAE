<?php

namespace App\Repositories\Eloquent;

use App\Models\Notificacion;
use App\Repositories\Interfaces\NotificacionRepositoryInterface;
use Illuminate\Support\Collection;

class NotificacionRepository implements NotificacionRepositoryInterface
{
    public function create(array $data): Notificacion
    {
        return Notificacion::create($data);
    }

    public function getByDestinatario(int $profesionalId): Collection
    {
        return Notificacion::where('fk_id_profesional_destinatario', $profesionalId)
            ->with(['origen.persona', 'evento', 'planDeAccion', 'intervencion'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function countNoLeidas(int $profesionalId): int
    {
        return Notificacion::where('fk_id_profesional_destinatario', $profesionalId)
            ->where('leida', false)
            ->count();
    }

    public function marcarLeida(int $notificacionId, int $profesionalId): void
    {
        Notificacion::where('id_notificacion', $notificacionId)
            ->where('fk_id_profesional_destinatario', $profesionalId)
            ->update(['leida' => true]);
    }

    public function marcarTodasLeidas(int $profesionalId): void
    {
        Notificacion::where('fk_id_profesional_destinatario', $profesionalId)
            ->where('leida', false)
            ->update(['leida' => true]);
    }
}
