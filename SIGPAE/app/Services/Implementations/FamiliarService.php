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

    /**
     * Recibe un array de datos (del wizard), gestiona la Persona asociada
     * y crea o actualiza el Familiar.
     */
    public function crearOActualizarDesdeArray(array $datos): Familiar
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
        
        // Plan A: Buscar por ID
        if (!empty($datos['fk_id_persona'])) {
            $persona = $this->personaService->getPersonaById($datos['fk_id_persona']); 
        }

        // 2. Plan B: Si falló el ID, Buscar por DNI (La Red de Seguridad)
        if (!$persona) {
            $persona = $this->personaService->findPersonaByDni($datos['dni']);
        }

        // 3. Ejecución Final: Decidir si Actualizar o Crear (UNA SOLA VEZ)
        if ($persona) {
            $this->personaService->updatePersona($persona->id_persona, $personaData);
            // Refrescamos el modelo para devolverlo actualizado
            $persona->refresh();
        } else {
            $persona = $this->personaService->createPersona($personaData);
        }

        // 2. Gestionar el FAMILIAR
        // Preparamos los datos del familiar
        $familiarData = [
            'fk_id_persona' => $persona->id_persona, // Vinculamos con la persona (vieja o nueva)
            'telefono_personal' => $datos['telefono_personal'] ?? null,
            'telefono_laboral' => $datos['telefono_laboral'] ?? null,
            'lugar_de_trabajo' => $datos['lugar_de_trabajo'] ?? null,
        ];

        $familiar = null;

        // Si viene 'id_familiar', actualizamos
        if (!empty($datos['id_familiar'])) {
            $familiar = $this->familiarRepository->find($datos['id_familiar']);
            if ($familiar) {
                $familiar = $this->familiarRepository->update($familiar->id_familiar, $familiarData);
            }
        }

        // Si no existe, creamos
        if (!$familiar) {
            $familiar = $this->familiarRepository->create($familiarData);
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

    public function createFamiliar(array $data): Familiar
    {
        return DB::transaction(function () use ($data) {
            $idPersona = $data['fk_id_persona'] ?? null;

            if (empty($idPersona)) {
                // Si no hay ID, intentamos crear la persona con TODOS los datos.
                // El Service/Repo de Persona sabrá ignorar los campos que no son suyos (como 'parentesco')
                // gracias al $fillable del modelo Persona.
                try {
                    $persona = $this->personaService->createPersona($data);
                    $idPersona = $persona->id_persona;
                } catch (\Exception $e) {
                    throw new InvalidArgumentException('No se pudo crear la persona asociada: ' . $e->getMessage());
                }
            }

            // Inyectamos el ID que acabamos de resolver (o que ya venía)
            $data['fk_id_persona'] = $idPersona;
            
            return $this->familiarRepository->create($data);
        });
    }

    public function updateFamiliar(int $id, array $data): Familiar
    {
        $familiar = $this->getFamiliarById($id);

        if (!$familiar) {
            throw new InvalidArgumentException('Familiar no encontrado');
        }

        // Si hay datos de persona, actualizamos también la persona asociada, excluyendo campos
        // no pertinentes segun marca el $fillable en los modelos Persona y Familiar
        return DB::transaction(function () use ($familiar, $data, $id) {
            if ($familiar->fk_id_persona) {
                $this->personaService->updatePersona($familiar->fk_id_persona, $data);
            }
            return $this->familiarRepository->update($id, $data);
        });
    }
}
