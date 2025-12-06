<?php

namespace App\Services\Implementations;

use App\Models\Persona;
use App\Repositories\Interfaces\PersonaRepositoryInterface;
use App\Services\Interfaces\PersonaServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class PersonaService implements PersonaServiceInterface
{
    public function __construct(
        private readonly PersonaRepositoryInterface $personaRepository
    ) {}

    public function getAllPersonas(): Collection
    {
        return $this->personaRepository->all();
    }

    public function getPersonaById(int $id, array $relations = []): ?Persona
    {
        return $this->personaRepository->findWithRelations($id, $relations);
    }

    public function createPersona(array $data): Persona
    {
        if (isset($data['fecha_nacimiento'])) {
             $formato = str_contains($data['fecha_nacimiento'], '/') ? 'd/m/Y' : 'Y-m-d';
             $fecha = \DateTime::createFromFormat($formato, $data['fecha_nacimiento']);
             $data['fecha_nacimiento'] = $fecha ? $fecha->format('Y-m-d') : null;
        }

        $validator = Validator::make($data, [
            'nombre'            => 'required|string|max:255',
            'apellido'          => 'required|string|max:255',
            'dni'               => 'required|string|unique:personas,dni',
            'fecha_nacimiento'  => 'required|date',
            'domicilio'         => 'nullable|string|max:255',
            'nacionalidad'      => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        return $this->personaRepository->create($data);
    }

    public function updatePersona(int $id, array $data): Persona
    {
        $validator = Validator::make($data, [
            'nombre'            => 'sometimes|string|max:255',
            'apellido'          => 'sometimes|string|max:255',
            'dni'               => 'sometimes|string|unique:personas,dni,' . $id . ',id_persona',
            'fecha_nacimiento'  => 'sometimes|date',
            'domicilio'         => 'nullable|string|max:255',
            'nacionalidad'      => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        return $this->personaRepository->update($id, $data);
    }

    public function deletePersona(int $id): bool
    {
        return $this->personaRepository->delete($id);
    }

    public function findPersonaByDni(string $dni): ?Persona
    {
        return $this->personaRepository->findByDni($dni);
    }

    public function getPersonasWithRelations(array $relations = []): Collection
    {
        return $this->personaRepository->allWithRelations($relations);
    }

    public function getPersonaByIdWithRelations(int $id, array $relations): ?Persona
    {
        return $this->personaRepository->findWithRelations($id, $relations);
    }
}
