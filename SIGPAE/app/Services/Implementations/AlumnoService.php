<?php
namespace App\Services\Implementations;

use App\Models\Alumno;
use App\Models\Aula;
use App\Services\Interfaces\AlumnoServiceInterface;
use App\Repositories\Interfaces\AlumnoRepositoryInterface;
use Illuminate\Http\Request;

//Define qué se hace (ej: listar, activar, eliminar, filtrar…)
//Pero no cómo se accede a la base de datos.
// Delegará el acceso de datos al Repository.
class AlumnoService implements AlumnoServiceInterface
{
    protected AlumnoRepositoryInterface $repo;

    public function __construct(AlumnoRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function listar(): \Illuminate\Support\Collection
    {
        return $this->repo->obtenerTodos();
    }

    public function crearAlumno(array $data): Alumno
    {
        try{
            $fecha = \DateTime::createFromFormat('d/m/Y', $data['fecha_nacimiento']);
            $data['fecha_nacimiento'] = $fecha ? $fecha->format('Y-m-d') : null;

            $persona = Persona::create([
                'dni' => $data['dni'],
                'nombre' => $data['nombre'],
                'apellido' => $data['apellido'],
                'fecha_nacimiento' => $data['fecha_nacimiento'],
                'nacionalidad' => $data['nacionalidad'],
                'activo' => true,
            ]);

            if (!$persona) {
                throw new \Exception('Error al crear la persona asociada');
            }

            if (!str_contains($data['aula'], '°')) {
                throw new \Exception('Formato de aula inválido. Ejemplo esperado: "3°A".');
            }

            [$curso, $division] = explode('°', $data['aula']);
            $aula = Aula::where('curso', $curso)->where('division', $division)->first();
            if (!$aula) {
                throw new \Exception("No se encontró el aula {$data['aula']}");
            }

            $cud = $data['cud'] === 'Sí' ? 1 : 0;

            return $this->repo->crear([
                'fk_persona' => $persona->id_persona,
                'fk_aula' => $aula->id_aula,
                'cud' => $cud,
                'inasistencias' => $data['inasistencias'] ?? null,
                'situacion_socioeconomica' => $data['situacion_socioeconomica'] ?? null,
                'situacion_familiar' => $data['situacion_familiar'] ?? null,
                'situacion_medica' => $data['situacion_medica'] ?? null,
                'situacion_escolar' => $data['situacion_escolar'] ?? null,
                'actividades_extraescolares' => $data['actividades_extraescolares'] ?? null,
                'intervenciones_externas' => $data['intervenciones_externas'] ?? null,
                'antecedentes' => $data['antecedentes'] ?? null,
                'observaciones' => $data['observaciones'] ?? null,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Error al crear alumno: '.$e->getMessage(), ['data' => $data]);
            throw new \Exception('Ocurrió un error al crear el alumno. '.$e->getMessage());
        }
    }

    public function eliminar(int $id): bool
    {
        return $this->repo->eliminar($id);
    }

    public function obtener(int $id): ?Alumno
    {
        return $this->repo->buscarPorId($id);
    }

    public function cambiarActivo(int $id): bool
    {
        return $this->repo->cambiarActivo($id);
    }

    // Nueva función: lógica de búsqueda y filtrado
    public function filtrar(Request $request): \Illuminate\Support\Collection
    {
        $query = Alumno::with('persona', 'aula');

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

        if ($request->filled('aula') && str_contains($request->aula, '°')) {
            [$curso, $division] = explode('°', $request->aula);
            $query->whereHas('aula', fn($q) =>
                $q->where('curso', $curso)->where('division', $division)
            );
        }
        
        $estado = $request->get('estado', 'activos');
        if ($estado === 'activos') {
            $query->whereHas('persona', fn($q) => $q->where('activo', true));
        } elseif ($estado === 'inactivos') {
            $query->whereHas('persona', fn($q) => $q->where('activo', false));
        }

        $alumnos = $query->get();

        // Ordenamiento compuesto: primero activos (desc), luego apellido asc dentro de cada grupo.
        $alumnos = $alumnos->sort(function ($a, $b) {
            $activoA = data_get($a, 'persona.activo') ? 1 : 0;
            $activoB = data_get($b, 'persona.activo') ? 1 : 0;

            // Queremos activos arriba 
            if ($activoA !== $activoB) {
                return $activoA > $activoB ? -1 : 1;
            }

            //orden alfabético
            $nombreA = mb_strtolower(data_get($a, 'persona.nombre', ''));
            $nombreB = mb_strtolower(data_get($b, 'persona.nombre', ''));

            return $nombreA <=> $nombreB;
        })->values();

        return $alumnos;
    }

    public function obtenerCursos(): \Illuminate\Support\Collection
    {
        return Aula::all()->map(fn($a) => $a->descripcion)->unique();
    }

    private function normalizarTexto(string $texto): string
    {
        return strtolower(strtr(iconv('UTF-8', 'ASCII//TRANSLIT', $texto), "´`^~¨", "     "));
    }
}