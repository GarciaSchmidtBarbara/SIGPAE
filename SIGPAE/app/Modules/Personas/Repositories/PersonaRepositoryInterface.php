<?php

namespace App\Modules\Personas\Repositories;

interface PersonaRepositoryInterface
{
    public function all(array $filters = []);

    public function find(int $id);

    public function create(array $data);

    public function update(int $id, array $data);

    public function delete(int $id): bool;
}
