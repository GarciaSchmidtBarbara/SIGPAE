<?php

namespace App\Modules\User\Services\Interfaces;

use App\Modules\User\Models\Profesional;
use Illuminate\Database\Eloquent\Collection;

interface ProfesionalServiceInterface
{
    public function getAllProfesionales(): Collection;
    public function getProfesionalById(int $id): ?Profesional;
    /**
     * Create a profesional. The $data array may contain both persona fields
     * (nombre, apellido, dni, fecha_nacimiento, domicilio, nacionalidad)
     * and profesional fields (matricula, especialidad, cargo, etc.).
     */
    public function createProfesional(array $data): Profesional;

    /**
     * Update a profesional. $data may include persona fields to update the linked Persona.
     */
    public function updateProfesional(int $id, array $data): Profesional;
    public function deleteProfesional(int $id): bool;
    public function getProfesionalByMatricula(string $matricula): ?Profesional;
    public function getProfesionalWithPersona(int $id): ?Profesional;
    public function getAllProfesionalesWithPersona(): Collection;
}