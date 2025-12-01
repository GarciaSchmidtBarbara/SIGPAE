<?php

namespace App\Services\Interfaces;

use Illuminate\Support\Collection as ISupportCollection;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Profesional;

interface ProfesionalServiceInterface
{
    public function getAllProfesionales(): Collection;
    public function getProfesionalById(int $id): ?Profesional;
    /**
     * Create a profesional. The $data array may contain both persona fields
     * (nombre, apellido, dni, fecha_nacimiento, domicilio, nacionalidad)
     * and profesional fields (matricula, especialidad, cargo, etc.).
     */
    public function crearProfesional(array $data): Profesional;

    /**
     * Update a profesional. $data may include persona fields to update the linked Persona.
     */
    public function updateProfesional(int $id, array $data): Profesional;
    public function deleteProfesional(int $id): bool;
    public function getProfesionalByMatricula(string $matricula): ?Profesional;
    public function getProfesionalWithPersona(int $id): ?Profesional;
    public function getAllProfesionalesWithPersona(): Collection;
    public function obtenerTodasLasSiglas(): ISupportCollection;
}