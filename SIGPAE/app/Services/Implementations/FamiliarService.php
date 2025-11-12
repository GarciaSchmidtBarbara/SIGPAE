<?php

namespace App\Services\Implementations;

use App\Models\Familiar;
use InvalidArgumentException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Collection;
use App\Services\Interfaces\PersonaServiceInterface;
use App\Services\Interfaces\FamiliarServiceInterface;
use App\Repositories\Interfaces\FamiliarRepositoryInterface;


class FamiliarService implements FamiliarServiceInterface
{
    protected $familiarRepository;
    protected $personaService;

    public function __construct(
        FamiliarRepositoryInterface $familiarRepository,
        PersonaServiceInterface $personaService
    ) {
        $this->familiarRepository = $familiarRepository;
        $this->personaService = $personaService;
    }

    public function getAllFamiliares(): Collection
    {
        return $this->familiarRepository->all();
    }

    public function getFamiliarById(int $id): ?Familiar
    {
        return $this->familiarRepository->find($id);
    }

    public function createFamiliar(array $data): Familiar
    {
        $personaFields = array_intersect_key($data, array_flip([
            'nombre', 'apellido', 'dni', 'fecha_nacimiento', 'domicilio', 'nacionalidad'
        ]));

        $familiarFields = array_intersect_key($data, array_flip([
            'fk_id_persona', 'lugar_de_trabajo', 'observaciones', 'telefono_personal', 'telefono_laboral', 'lugar_de_trabajo', 'otro_parentesco', 'parentesco'
        ]));

        try {
            DB::beginTransaction();

            if (!empty($data['fk_id_persona'])) {
                $familiarFields['fk_id_persona'] = $data['fk_id_persona'];
            }
            else if (!empty($personaFields)) {
                $persona = $this->personaService->createPersona($personaFields);
                $familiarFields['fk_id_persona'] = $persona->id_persona;

            } 
            else {
                throw new InvalidArgumentException('Se requiere fk_id_persona o datos de persona');
            }

            $familiar = $this->familiarRepository->create($familiarFields);

            DB::commit();
            return $familiar;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateFamiliar(int $id, array $data): Familiar
    {
        $familiar = $this->getFamiliarById($id);
        if (!$familiar) {
            throw new InvalidArgumentException('Familiar no encontrado');
        }
        $personaFields = array_intersect_key($data, array_flip([
            'nombre', 'apellido', 'dni', 'fecha_nacimiento', 'domicilio', 'nacionalidad'
        ]));

        $familiarFields = array_intersect_key($data, array_flip([
            'lugar_de_trabajo', 'observaciones', 'telefono_personal', 'telefono_laboral', 'lugar_de_trabajo', 'otro_parentesco', 'parentesco'
        ]));

        try {
            DB::beginTransaction();
             if (!empty($personaFields)) {
            $personaId = $familiar->fk_id_persona ?? null;
            if (!$personaId) {
                 throw new InvalidArgumentException('Familiar no tiene persona asociada para actualizar');
            }
             $this->personaService->updatePersona($personaId, $personaFields);
            }
            if (!empty($familiarFields)) {
                $familiar = $this->familiarRepository->update($id, $familiarFields);
            }


            DB::commit();
            return $familiar;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteFamiliar(int $id): bool
    {
        return $this->familiarRepository->delete($id);
    }

    public function getFamiliarWithPersona(int $id): ?Familiar
    {
        return $this->familiarRepository->findWithPersona($id);
    }

    public function getAllFamiliaresWithPersona(): Collection
    {
        return $this->familiarRepository->allWithPersona();
    }
}
