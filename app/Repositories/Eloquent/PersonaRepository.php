<?php

namespace App\Repositories\Eloquent;

use App\Models\Persona;
use App\Repositories\Interfaces\PersonaRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PersonaRepository implements PersonaRepositoryInterface
{
    protected Persona $model;

    public function __construct(Persona $persona)
    {
        $this->model = $persona;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function find(int $id): ?Persona
    {
        return $this->model->find($id);
    }

    public function create(array $data): Persona
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Persona
    {
        $persona = $this->model->findOrFail($id);
        $persona->update($data);
        return $persona->fresh();
    }

    public function delete(int $id): bool
    {
        $persona = $this->model->find($id);
        if (!$persona) {
            return false;
        }
        return (bool) $persona->delete();
    }

    public function findByDni(string $dni): ?Persona
    {
        return $this->model->where('dni', $dni)->first();
    }

    public function findWithRelations(int $id, array $relations = []): ?Persona
    {
        return $this->model->with($relations)->find($id);
    }

    public function allWithRelations(array $relations = []): Collection
    {
        return $this->model->with($relations)->get();
    }
}
