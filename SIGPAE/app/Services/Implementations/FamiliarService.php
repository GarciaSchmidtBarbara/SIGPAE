<?php

namespace App\Services\Implementations;

use App\Models\Familiar;
use App\Models\Persona;
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

    /**
     * Recibe un array de datos (del wizard), gestiona la Persona asociada
     * y crea o actualiza el Familiar.
     */
    public function crearOActualizarDesdeArray(array $datos): \App\Models\Familiar
    {
        // 1. Gestionar la PERSONA
        // Preparamos los datos de la persona
        $personaData = [
            'dni' => $datos['dni'],
            'nombre' => $datos['nombre'],
            'apellido' => $datos['apellido'],
            'fecha_nacimiento' => $datos['fecha_nacimiento'],
            'nacionalidad' => $datos['nacionalidad'] ?? null,
            'domicilio' => $datos['domicilio'] ?? null,
            'activo' => true
        ];

        $persona = null;
        
        // 1. Plan A: Buscar por ID (Si viene en la sesión)
        if (!empty($datos['fk_id_persona'])) {
            $persona = \App\Models\Persona::find($datos['fk_id_persona']);
        } 

        // 2. Plan B: Si falló el ID, Buscar por DNI (La Red de Seguridad)
        if (!$persona) {
            $persona = \App\Models\Persona::where('dni', $datos['dni'])->first();
        }

        // 3. Ejecución Final: Decidir si Actualizar o Crear (UNA SOLA VEZ)
        if ($persona) {
            $persona->update($personaData);
        } else {
            $persona = \App\Models\Persona::create($personaData);
        }

        // 2. Gestionar el FAMILIAR
        // Preparamos los datos del familiar
        $familiarData = [
            'fk_id_persona' => $persona->id_persona, // Vinculamos con la persona (vieja o nueva)
            'telefono_personal' => $datos['telefono_personal'] ?? null,
            'telefono_laboral' => $datos['telefono_laboral'] ?? null,
            'lugar_de_trabajo' => $datos['lugar_de_trabajo'] ?? null,
            'parentesco' => strtoupper($datos['parentesco']),
            'otro_parentesco' => $datos['otro_parentesco'] ?? null,
            // Nota: Las observaciones ACÁ son las propias del familiar (si tu tabla lo tiene),
            // no las de la relación con el alumno.
        ];

        $familiar = null;

        // Si viene 'id_familiar', actualizamos
        if (!empty($datos['id_familiar'])) {
            $familiar = \App\Models\Familiar::find($datos['id_familiar']);
            if ($familiar) {
                $familiar->update($familiarData);
            }
        }

        // Si no existe, creamos
        if (!$familiar) {
            $familiar = \App\Models\Familiar::create($familiarData);
        }

        return $familiar;
    }


    public function getAllFamiliares(): Collection
    {
        return $this->familiarRepository->all();
    }

    public function getFamiliarById(int $id): ?Familiar
    {
        return $this->familiarRepository->find($id);
    }

    public function getFamiliarWithPersona(int $id): ?Familiar
    {
        return $this->familiarRepository->findWithPersona($id);
    }

    public function getAllFamiliaresWithPersona(): Collection
    {
        return $this->familiarRepository->allWithPersona();
    }

    public function deleteFamiliar(int $id): bool
    {
        return $this->familiarRepository->delete($id);
    }

    // Método Legacy: Crea usando transacción y separando campos (usado por otras vistas)
    public function createFamiliar(array $data): Familiar
    {
        // Filtramos campos para Persona
        $personaFields = array_intersect_key($data, array_flip([
            'nombre', 'apellido', 'dni', 'fecha_nacimiento', 'domicilio', 'nacionalidad'
        ]));

        // Filtramos campos para Familiar
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

    // Método Legacy: Actualiza usando transacción
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
                if ($personaId) {
                    $this->personaService->updatePersona($personaId, $personaFields);
                }
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
}
