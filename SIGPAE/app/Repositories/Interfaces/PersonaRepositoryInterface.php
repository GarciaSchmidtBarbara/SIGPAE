<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use App\Modules\Common\Models\Persona;

interface PersonaRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): ?Persona;
    public function create(array $data): Persona;
    public function update(int $id, array $data): Persona;
    public function delete(int $id): bool;
    public function findByDni(string $dni): ?Persona;
    public function findWithRelations(int $id, array $relations = []): ?Persona;
    public function allWithRelations(array $relations = []): Collection;
}