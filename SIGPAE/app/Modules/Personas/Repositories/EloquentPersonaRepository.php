<?php

namespace App\Modules\Personas\Repositories;

use App\Modules\Personas\Models\Persona;

class EloquentPersonaRepository implements PersonaRepositoryInterface
{
    protected $model;

    public function __construct(Persona $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = [])
    {
        $query = $this->model->newQuery();
        // apply simple filters
        foreach ($filters as $k => $v) {
            $query->where($k, $v);
        }

        return $query->get();
    }

    public function find(int $id)
    {
        return $this->model->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data)
    {
        $record = $this->model->findOrFail($id);
        $record->update($data);
        return $record;
    }

    public function delete(int $id): bool
    {
        $record = $this->model->find($id);
        if (! $record) {
            return false;
        }

        return (bool) $record->delete();
    }
}
