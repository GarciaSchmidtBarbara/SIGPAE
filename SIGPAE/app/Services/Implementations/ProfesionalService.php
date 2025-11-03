<?php

namespace App\Services\Implementations;

use App\Services\Interfaces\PersonaServiceInterface;
use App\Models\Profesional;
use App\Repositories\Interfaces\ProfesionalRepositoryInterface;
use App\Services\Interfaces\ProfesionalServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class ProfesionalService implements ProfesionalServiceInterface
{
    protected $profesionalRepository;
    protected $personaService;

    public function __construct(
        ProfesionalRepositoryInterface $profesionalRepository,
        PersonaServiceInterface $personaService
    ) {
        $this->profesionalRepository = $profesionalRepository;
        $this->personaService = $personaService;
    }

    public function getAllProfesionales(): Collection
    {
        return $this->profesionalRepository->all();
    }

    public function getProfesionalById(int $id): ?Profesional
    {
        return $this->profesionalRepository->find($id);
    }

    public function createProfesional(array $data): Profesional
    {
        // Separar campos de persona y campos propios del profesional
        $personaFields = array_intersect_key($data, array_flip([
            'nombre', 'apellido', 'dni', 'fecha_nacimiento', 'domicilio', 'nacionalidad'
        ]));

        $profesionalFields = array_intersect_key($data, array_flip([
            'telefono', 'usuario', 'email', 'password', 'fk_id_persona'
        ]));

        try {
            DB::beginTransaction();

            // Si vienen datos de persona, crear la Persona y asociarla.
            if (!empty($personaFields)) {
                $persona = $this->personaService->createPersona($personaFields);
                $profesionalFields['fk_id_persona'] = $persona->id_persona;
            } else {
                // Si no vienen datos de persona, esperamos que venga fk_id_persona en payload
                if (empty($profesionalFields['fk_id_persona'])) {
                    throw new InvalidArgumentException('Se requiere fk_id_persona o datos de persona');
                }
            }

            $profesional = $this->profesionalRepository->create($profesionalFields);

            DB::commit();
            return $profesional;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateProfesional(int $id, array $data): Profesional
    {
        $profesional = $this->getProfesionalById($id);
        if (!$profesional) {
            throw new InvalidArgumentException('Profesional no encontrado');
        }

        // Separar campos de persona y profesional
        $personaFields = array_intersect_key($data, array_flip([
            'nombre', 'apellido', 'dni', 'fecha_nacimiento', 'domicilio', 'nacionalidad'
        ]));

        $profesionalFields = array_intersect_key($data, array_flip([
            'matricula', 'especialidad', 'cargo', 'profesion', 'telefono', 'usuario', 'email', 'password'
        ]));

        try {
            DB::beginTransaction();

            // Si vienen campos de persona, actualizamos la Persona asociada
            if (!empty($personaFields)) {
                $fk = $profesional->fk_id_persona ?? null;
                if (!$fk) {
                    throw new InvalidArgumentException('Profesional no tiene persona asociada');
                }
                $this->personaService->updatePersona($fk, $personaFields);
            }

            // Actualizar el profesional con los campos propios
            if (!empty($profesionalFields)) {
                $profesional = $this->profesionalRepository->update($id, $profesionalFields);
            }

            DB::commit();
            return $profesional;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteProfesional(int $id): bool
    {
        return $this->profesionalRepository->delete($id);
    }

    public function getProfesionalByMatricula(string $matricula): ?Profesional
    {
        return $this->profesionalRepository->findByMatricula($matricula);
    }

    public function getProfesionalWithPersona(int $id): ?Profesional
    {
        return $this->profesionalRepository->findWithPersona($id);
    }

    public function getAllProfesionalesWithPersona(): Collection
    {
        return $this->profesionalRepository->allWithPersona();
    }
}
