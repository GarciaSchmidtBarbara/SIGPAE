<?php

namespace App\Modules\User\Repositories\Interfaces;

use App\Modules\User\Models\Evento;
use Illuminate\Database\Eloquent\Collection;

interface EventoRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): ?Evento;
    public function create(array $data): Evento;
    public function update(int $id, array $data): Evento;
    public function delete(int $id): bool;
}