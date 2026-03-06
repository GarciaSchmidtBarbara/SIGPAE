<?php

namespace App\Repositories\Interfaces;

use App\Models\Profesional;
use Illuminate\Database\Eloquent\Collection;

interface ProfesionalRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): ?Profesional;
    public function crear(array $data): Profesional;
    public function update(int $id, array $data): Profesional;
    public function delete(int $id): bool;
    public function findByPersona(int $personaId): ?Profesional;
    public function findWithPersona(int $id): ?Profesional;
    public function allWithPersona(): Collection;
    public function findByMatricula(string $matricula): ?Profesional;
    public function findByEmail(string $email): ?Profesional;
    public function filtrar(\Illuminate\Http\Request $request): \Illuminate\Contracts\Pagination\LengthAwarePaginator;
    public function existeUsuario(string $usuario): bool;
    public function buscarTokenReset(string $email): ?object;
    public function eliminarTokenReset(string $email): bool;
}