<?php

namespace App\Repositories\Eloquent;

use App\Models\Evento;
use App\Repositories\Interfaces\EventoRepositoryInterface;

class EventoRepository implements EventoRepositoryInterface
{
    public function find(int $id): ?Evento
    {
        return Evento::find($id);
    }

    public function all()
    {
        return Evento::query()->latest('fecha_hora')->get();
    }

    public function create(array $data): Evento
    {
        return Evento::create($data);
    }

    public function update(int $id, array $data): ?Evento
    {
        $evento = Evento::find($id);
        if (!$evento) {
            return null;
        }

        $evento->update($data);
        return $evento;
    }

    public function delete(int $id): bool
    {
        return (bool) Evento::whereKey($id)->delete();
    }

    public function getEventosByDateRange(string $start, string $end): \Illuminate\Support\Collection
    {
        return Evento::whereBetween('fecha_hora', [$start, $end])
            ->with(['profesionalCreador.persona'])
            ->orderBy('fecha_hora', 'asc')
            ->get();
    }
}
