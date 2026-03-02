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
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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
    public function filtrar(Request $request): LengthAwarePaginator
{
    $query = Profesional::with('persona');

    if ($request->filled('buscar')) {

        $buscar = strtolower($request->buscar);

        $query->where(function ($q) use ($buscar) {

            // Buscar en persona
            $q->whereHas('persona', function ($sub) use ($buscar) {
                $sub->whereRaw("LOWER(nombre) LIKE ?", ["%{$buscar}%"])
                    ->orWhereRaw("LOWER(apellido) LIKE ?", ["%{$buscar}%"])
                    ->orWhere("dni", 'like', "%{$buscar}%");
            })

            // Buscar en profesional
            ->orWhereRaw("LOWER(siglas) LIKE ?", ["%{$buscar}%"])
            ->orWhereRaw("LOWER(profesion) LIKE ?", ["%{$buscar}%"]);
        });
    }

    return $query
        ->orderBy('id_profesional', 'desc')
        ->paginate(10)
        ->withQueryString();
}

    public function crearProfesional(array $data): Profesional {
        \DB::beginTransaction();
        try {
            $formato = str_contains($data['fecha_nacimiento'], '/') ? 'd/m/Y' : 'Y-m-d';
            $fecha = \DateTime::createFromFormat($formato, $data['fecha_nacimiento']);
            $data['fecha_nacimiento'] = $fecha ? $fecha->format('Y-m-d') : null;

            $persona = Persona::create([
                'dni' => $data['dni'],
                'nombre' => $data['nombre'],
                'apellido' => $data['apellido'],
                'fecha_nacimiento' => $data['fecha_nacimiento'],
                'activo' => true,
            ]);

            if (!$persona) {
                throw new \Exception('Error al crear la persona asociada');
            }
                        
            $usuario = new Profesional([
                'fk_id_persona' => $persona->id_persona,
                'usuario' => $data['usuario'],
                'profesion' => $data['profesion'],
                'siglas' => $data['siglas'],
                'email' => $data['email'],
                'contrasenia' => bcrypt($data['contrasenia']),
            ]);

            $usuario->save();
            
            if (!$usuario->exists) {
                throw new \Exception('El usuario no se guardó correctamente');
            }
            \DB::commit();
            return $usuario->load(['persona']);

        } catch (\Throwable $e) {
            \DB::rollBack();
            \Log::error('Error al crear usuario: '.$e->getMessage(), ['data' => $data]);
            throw new \Exception('Ocurrió un error al crear el usuario. '.$e->getMessage());
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
