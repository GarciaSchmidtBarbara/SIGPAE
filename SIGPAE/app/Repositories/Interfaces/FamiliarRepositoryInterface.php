<?php

namespace App\Repositories\Interfaces;

use App\Modules\User\Models\Familiar;
use Illuminate\Database\Eloquent\Collection;

interface FamiliarRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): ?Familiar;
    public function create(array $data): Familiar;
    public function update(int $id, array $data): Familiar;
    public function delete(int $id): bool;
    public function findByPersona(int $personaId): ?Familiar;
    public function findWithPersona(int $id): ?Familiar;
    public function allWithPersona(): Collection;
}