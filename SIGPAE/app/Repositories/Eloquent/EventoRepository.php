<?php

namespace App\Repositories\Eloquent;

use App\Models\Evento;
use App\Repositories\Interfaces\EventoRepositoryInterface;
use Illuminate\Support\Collection;

class EventoRepository implements EventoRepositoryInterface
{
    public function find(int $eventoId, array $relations = []): ?Evento
    {
        $query = Evento::query();
        
        if (!empty($relations)) {
            $query->with($relations);
        }
        
        return $query->find($eventoId);
    }

    public function all(array $relations = []): Collection
    {
        $query = Evento::query();
        
        if (!empty($relations)) {
            $query->with($relations);
        }
        
        return $query->latest('fecha_hora')->get();
    }

    public function create(array $data): Evento
    {
        return Evento::create($data);
    }

    public function update(int $eventoId, array $data): bool
    {
        $evento = Evento::find($eventoId);
        if (!$evento) {
            return false;
        }

        return $evento->update($data);
    }

    public function delete(int $eventoId): bool
    {
        return (bool) Evento::whereKey($eventoId)->delete();
    }

    public function getByCreador(int $profesionalId): Collection
    {
        return Evento::where('fk_id_profesional_creador', $profesionalId)
            ->with(['profesionalCreador.persona'])
            ->latest('fecha_hora')
            ->get();
    }

    public function getAsistidosPorProfesional(int $profesionalId, bool $confirmado = null): Collection
    {
        $query = Evento::whereHas('esInvitadoA', function ($q) use ($profesionalId, $confirmado) {
            $q->where('fk_id_profesional', $profesionalId);
            if ($confirmado !== null) {
                $q->where('confirmado', $confirmado);
            }
        })->with(['profesionalCreador.persona', 'esInvitadoA']);

        return $query->latest('fecha_hora')->get();
    }

    public function syncProfesionales(Evento $evento, array $profesionalIds): void
    {
        // Esta lógica se maneja en el Service usando EsInvitadoA
        // Este método puede quedar vacío o implementarse según necesidad
    }

    public function syncAlumnos(Evento $evento, array $alumnoIds): void
    {
        $evento->alumnos()->sync($alumnoIds);
    }

    public function getEventosByDateRange(string $start, string $end): Collection
    {
        return Evento::whereBetween('fecha_hora', [$start, $end])
            ->with(['profesionalCreador.persona'])
            ->orderBy('fecha_hora', 'asc')
            ->get();
    }
}
