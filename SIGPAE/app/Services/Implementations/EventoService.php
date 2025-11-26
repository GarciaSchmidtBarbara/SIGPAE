<?php

namespace App\Services\Implementations;

use App\Models\Evento;
use App\Repositories\Interfaces\EventoRepositoryInterface;
use App\Services\Interfaces\EventoServiceInterface;
use Illuminate\Support\Collection;

class EventoService implements EventoServiceInterface
{
    protected EventoRepositoryInterface $repo;

    public function __construct(EventoRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function listarTodos(): Collection
    {
        return $this->repo->all();
    }

    public function obtenerPorId(int $id): ?Evento
    {
        return $this->repo->find($id);
    }

    public function crear(array $data): Evento
    {
        return $this->repo->create($data);
    }

    public function actualizar(int $id, array $data): bool
    {
        $evento = $this->repo->update($id, $data);
        return $evento !== null;
    }

    public function eliminar(int $id): bool
    {
        return $this->repo->delete($id);
    }

    public function obtenerEventosParaCalendario(string $start, string $end): array
    {
        $eventos = $this->repo->getEventosByDateRange($start, $end);
        
        return $eventos->map(function ($evento) {
            return [
                'id' => $evento->id_evento,
                'title' => $this->formatearTituloEvento($evento),
                'start' => $evento->fecha_hora->toIso8601String(),
                'extendedProps' => [
                    'tipo' => $evento->tipo_evento?->value ?? 'general',
                    'lugar' => $evento->lugar,
                    'notas' => $evento->notas,
                    'creador' => $evento->profesionalCreador?->persona?->nombre ?? 'Sin asignar',
                ],
            ];
        })->toArray();
    }

    private function formatearTituloEvento(Evento $evento): string
    {
        $tipo = $evento->tipo_evento?->name ?? 'Evento';
        $lugar = $evento->lugar ? " - {$evento->lugar}" : '';
        return "{$tipo}{$lugar}";
    }
}
