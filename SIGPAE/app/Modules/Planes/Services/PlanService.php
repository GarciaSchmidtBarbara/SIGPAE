<?php

namespace App\Modules\Planes\Services;

use App\Modules\Planes\Repositories\PlanRepositoryInterface;

class PlanService
{
    protected $repo;

    public function __construct(PlanRepositoryInterface $repo)
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
