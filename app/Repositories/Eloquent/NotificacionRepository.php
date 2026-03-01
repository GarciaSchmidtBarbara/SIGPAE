<?php

namespace App\Repositories\Eloquent;

use App\Models\Notificacion;
use App\Repositories\Interfaces\NotificacionRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class NotificacionRepository implements NotificacionRepositoryInterface
{
    private function cacheKey(int $profesionalId): string
    {
        return "notif_no_leidas_{$profesionalId}";
    }

    public function create(array $data): Notificacion
    {
        $notificacion = Notificacion::create($data);

        //Invalidar el conteo cacheado del destinatario ya que tiene una notificación nueva
        Cache::forget($this->cacheKey($data['fk_id_profesional_destinatario']));

        return $notificacion;
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
        return Cache::remember($this->cacheKey($profesionalId), 300, function () use ($profesionalId) {
            return Notificacion::where('fk_id_profesional_destinatario', $profesionalId)
                ->where('leida', false)
                ->count();
        });
    }

    public function marcarLeida(int $notificacionId, int $profesionalId): void
    {
        Notificacion::where('id_notificacion', $notificacionId)
            ->where('fk_id_profesional_destinatario', $profesionalId)
            ->update(['leida' => true]);

        Cache::forget($this->cacheKey($profesionalId));
    }

    public function marcarTodasLeidas(int $profesionalId): void
    {
        Notificacion::where('fk_id_profesional_destinatario', $profesionalId)
            ->where('leida', false)
            ->update(['leida' => true]);

        Cache::forget($this->cacheKey($profesionalId));
    }
}
