<?php

namespace App\Repositories\Eloquent;

use App\Models\Profesional;
use App\Repositories\Interfaces\ProfesionalRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ProfesionalRepository implements ProfesionalRepositoryInterface
{
    protected Profesional $model;

    public function __construct(Profesional $profesional)
    {
        $this->model = $profesional;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function find(int $id): ?Profesional
    {
        return $this->model->find($id);
    }

    public function create(array $data): Profesional
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Profesional
    {
        $profesional = $this->model->findOrFail($id);
        $profesional->update($data);
        return $profesional->fresh();
    }

    public function delete(int $id): bool
    {
        $profesional = $this->model->find($id);
        if (!$profesional) {
            return false;
        }
        return (bool) $profesional->delete();
    }

    public function findByPersona(int $personaId): ?Profesional
    {
        return $this->model->where('fk_id_persona', $personaId)->first();
    }

    public function findWithPersona(int $id): ?Profesional
    {
        return $this->model->with('persona')->find($id);
    }

    public function allWithPersona(): Collection
    {
        return $this->model->with('persona')->get();
    }

    public function findByMatricula(string $matricula): ?Profesional
    {
        return $this->model->where('matricula', $matricula)->first();
    }
}
