<?php

namespace App\Modules\Personas\Services;

use App\Modules\Personas\Repositories\PersonaRepositoryInterface;

class PersonaService
{
    protected $repo;

    public function __construct(PersonaRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function list(array $filters = [])
    {
        return $this->repo->all($filters);
    }

    public function get(int $id)
    {
        return $this->repo->find($id);
    }

    public function create(array $data)
    {
        return $this->repo->create($data);
    }

    public function update(int $id, array $data)
    {
        return $this->repo->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repo->delete($id);
    }
}
