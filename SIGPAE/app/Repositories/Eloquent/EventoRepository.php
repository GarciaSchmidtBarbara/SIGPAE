<?php

namespace App\Repositories;

use App\Models\Evento;
use Illuminate\Support\Collection;

class EventoRepository implements EventoRepositoryInterface
{
    private Evento $model;

    public function __construct(Evento $model)
    {
        $this->model = $model;
    }
    public function all(array $relations = []): Collection
    {
        return $this->model->with($relations)->get();
    }
    public function find(int $eventoId, array $relations = []): ?Evento
    {
        return $this->model->with($relations)->find($eventoId);
    }
    public function create(array $data): Evento
    {
        return $this->model->create($data);
    }
    public function update(int $eventoId, array $data): bool
    {
        $evento = $this->model->find($eventoId);
        if ($evento) {
            return $evento->update($data);
        }
        return false;
    }
    public function delete(int $eventoId): bool
    {
        $evento = $this->model->find($eventoId);
        if ($evento) {
            return $evento->delete();
        }
        return false;
    }

    public function getByCreador(int $profesionalId): Collection
    {
        return $this->model->where('Fk_profesional_creador', $profesionalId)->get();
    }

    public function getAsistidosPorProfesional(int $profesionalId, bool $confirmado = null): Collection
    {
        $query = $this->model->whereHas('profesionalesAsistentes', function ($q) use ($profesionalId, $confirmado) {
            $q->where('id_profesional', $profesionalId);
            
            if (!is_null($confirmado)) {
                $q->where('asistencia_confirmada', $confirmado);
            }
        });

        return $query->get();
    }

    public function syncProfesionales(Evento $evento, array $profesionalIds): void
    {
        $evento->agregarProfesionales($profesionalIds);
    }

    public function syncAlumnos(Evento $evento, array $alumnoIds): void
    {
        $evento->agregarAlumnos($alumnoIds);
    }
}