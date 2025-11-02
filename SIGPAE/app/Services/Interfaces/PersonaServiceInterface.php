<?php

namespace App\Services\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use App\Modules\Common\Models\Persona;

interface PersonaServiceInterface
{
    public function getAllPersonas(): Collection;
    public function getPersonaById(int $id, array $relations = []): ?Persona;
    public function createPersona(array $data): Persona;
    public function updatePersona(int $id, array $data): Persona;
    public function deletePersona(int $id): bool;
    public function findPersonaByDni(string $dni): ?Persona;
    public function getPersonasWithRelations(array $relations = []): Collection;
    public function getPersonaByIdWithRelations(int $id, array $relations): ?Persona;
}