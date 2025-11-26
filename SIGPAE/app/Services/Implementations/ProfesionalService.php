<?php

namespace App\Services\Implementations;
// Imports
// Profesional
use App\Repositories\Interfaces\ProfesionalRepositoryInterface;
use App\Services\Interfaces\ProfesionalServiceInterface;
use App\Models\Profesional;
// Persona
use App\Services\Interfaces\PersonaServiceInterface;
use App\Models\Persona;
// Illuminate
use Illuminate\Support\Collection as ISupportCollection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use InvalidArgumentException;
use App\Enums\Siglas;

class ProfesionalService implements ProfesionalServiceInterface
{
    protected ProfesionalRepositoryInterface $repo;
    protected \App\Services\Interfaces\PersonaServiceInterface $personaService;

    public function __construct(ProfesionalRepositoryInterface $repo, \App\Services\Interfaces\PersonaServiceInterface $personaService) {
        $this->repo = $repo;
        $this->personaService = $personaService;
    }

    public function getAllProfesionales(): Collection
    {
        return $this->repo->all();
    }

    public function getProfesionalById(int $id): ?Profesional
    {
        return $this->repo->find($id);
    }

    public function cambiarActivo(int $id): bool
    {
        return $this->repo->cambiarActivo($id);
    }

    // Lógica de búsqueda y filtrado
    public function filtrar(Request $request): ISupportCollection {
        $query = Profesional::with('persona');

        if ($request->filled('nombre')) {
            $nombre = $this->normalizarTexto($request->nombre);
            $query->whereHas('persona', fn($q) =>
                $q->whereRaw("LOWER(unaccent(nombre::text)) LIKE ?", ["%{$nombre}%"])
            );
        }

        if ($request->filled('apellido')) {
            $apellido = $this->normalizarTexto($request->apellido);
            $query->whereHas('persona', fn($q) =>
                $q->whereRaw("LOWER(unaccent(apellido)) LIKE ?", ["%{$apellido}%"])
            );
        }

        if ($request->filled('documento')) {
            $query->whereHas('persona', fn($q) =>
                $q->where('dni', 'like', '%' . $request->documento . '%')
            );
        }

        if ($request->filled('profesion')) {
            $query->where('siglas', $request->profesion);
        }

        $usuarios = $query->get();

        return $usuarios;
    }

    public function createProfesional(array $data): Profesional
    {
        // Separar campos de persona y campos propios del profesional
        $personaFields = array_intersect_key($data, array_flip([
            'nombre', 'apellido', 'dni', 'fecha_nacimiento', 'domicilio', 'nacionalidad'
        ]));

        $profesionalFields = array_intersect_key($data, array_flip([
            'telefono', 'usuario', 'email', 'contrasenia', 'profesion', 'siglas', 'fk_id_persona'
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
            
            $profesional = $this->repo->create($profesionalFields);
            
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
            'matricula', 'especialidad', 'cargo', 'profesion', 'siglas', 'telefono', 'usuario', 'email', 'contrasenia'
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
                $profesional = $this->repo->update($id, $profesionalFields);
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
        return $this->repo->delete($id);
    }

    public function getProfesionalByMatricula(string $matricula): ?Profesional
    {
        return $this->repo->findByMatricula($matricula);
    }

    public function getProfesionalWithPersona(int $id): ?Profesional
    {
        return $this->repo->findWithPersona($id);
    }

    public function getAllProfesionalesWithPersona(): Collection
    {
        return $this->repo->allWithPersona();
    }
    public function obtenerTodasLasSiglas(): ISupportCollection {
        // Devuelve una colección con todas las siglas posibles del enum
        return collect(Siglas::cases())->map(fn($sigla) => $sigla->value);
    }

    private function normalizarTexto(string $texto): string {
        return strtolower(strtr(iconv('UTF-8', 'ASCII//TRANSLIT', $texto), "´`^~¨", "     "));
    }
}
