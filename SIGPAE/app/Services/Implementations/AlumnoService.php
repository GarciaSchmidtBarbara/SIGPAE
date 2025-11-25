<?php
namespace App\Services\Implementations;

use App\Repositories\Interfaces\AlumnoRepositoryInterface;
use App\Services\Interfaces\AlumnoServiceInterface;
//Familiares
use App\Services\Interfaces\FamiliarServiceInterface;
// Models
use App\Models\Alumno;
use App\Models\Aula;
use App\Models\Persona;
use Illuminate\Http\Request;
// Soportes
use Illuminate\Support\Facades\DB;

//Define qué se hace (ej: listar, activar, eliminar, filtrar…)
//Pero no cómo se accede a la base de datos.
// Delegará el acceso de datos al Repository.
class AlumnoService implements AlumnoServiceInterface
{
    protected AlumnoRepositoryInterface $repo;

    public function __construct(AlumnoRepositoryInterface $repo, FamiliarServiceInterface $familiarService)
    {
        $this->repo = $repo;
        $this->familiarService = $familiarService;
    }

    public function listar(): \Illuminate\Support\Collection
    {
        return $this->repo->obtenerTodos();
    }

    public function crearAlumno(array $data): Alumno
    {
        try {
            $formato = str_contains($data['fecha_nacimiento'], '/') ? 'd/m/Y' : 'Y-m-d';
            $fecha = \DateTime::createFromFormat($formato, $data['fecha_nacimiento']);
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
            $aula = Aula::where('curso', $curso)
                        ->where('division', $division)
                        ->first();

            if (!$aula) {
                throw new \Exception('No se encontró el aula con la descripción: ' . $data['aula']);
            }
            
            $cud = $data['cud'] === 'Sí' ? 1 : 0;
            
            $alumno = new Alumno([
                'fk_id_persona' => $persona->id_persona,
                'fk_id_aula' => $aula->id_aula,
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

            $alumno->save();
            
            if (!$alumno->exists) {
                throw new \Exception('El alumno no se guardó correctamente');
            }

            return $alumno;
        } catch (\Throwable $e) {
            \Log::error('Error al crear alumno: '.$e->getMessage(), ['data' => $data]);
            throw new \Exception('Ocurrió un error al crear el alumno. '.$e->getMessage());
        }
    }
    
    //Cuando se crea el alumno junto con sus familiares
    public function crearAlumnoConFamiliares(array $datosAlumno, array $listaFamiliares): Alumno
    {
        return DB::transaction(function () use ($datosAlumno, $listaFamiliares) {
            
            // 1. Crear Alumno (Usamos tu método existente)
            $alumno = $this->crearAlumno($datosAlumno); 

            // 2. Procesar la lista de familiares/hermanos
            $this->procesarRelaciones($alumno, $listaFamiliares);

            return $alumno;
        });
    }

    public function buscar(string $q): \Illuminate\Support\Collection
    {
        if (trim($q) === '') return collect();
        $like = '%' . str_replace('%','', $q) . '%';
        return Alumno::with(['persona','aula'])
            ->whereHas('persona', function($sub) use ($like){
                $sub->where('dni','like',$like)
                    ->orWhere('nombre','ilike',$like)
                    ->orWhere('apellido','ilike',$like);
            })
            ->limit(10)
            ->get();
    }

    public function eliminar(int $id): bool
    {
        return $this->repo->eliminar($id);
    }

    public function obtener(int $id): ?Alumno
    {
        return $this->repo->buscarPorId($id);
    }

    public function obtenerParaEditar(int $id): ?Alumno
    {
        return $this->repo->buscarParaEditar($id);
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

    private function procesarRelaciones(Alumno $alumno, array $listaFamiliares)
    {
        foreach ($listaFamiliares as $datos) {
            
            $esHermanoAlumno = !empty($datos['fk_id_persona']) && !empty($datos['asiste_a_institucion']);

            // Extraemos la observación para la tabla PIVOTE
            $observacionPivot = $datos['observaciones'] ?? null;

            if ($esHermanoAlumno) {
                // 1. Buscamos al hermano
                $hermano = $this->repo->buscarPorPersonaId($datos['fk_id_persona']);
                // (O usá Alumno::where si no querés crear método en repo para esto solo)

                if ($hermano && $hermano->id_alumno !== $alumno->id_alumno) {
                    
                    // 2. DELEGAMOS LA VINCULACIÓN AL REPOSITORIO
                    // Le decimos: "Vinculá A con B de forma segura y guardá la observación"
                    $this->repo->vincularHermanos(
                        $alumno->id_alumno, 
                        $hermano->id_alumno, 
                        $observacionPivot
                    );
                }

            } else {
                // --- CAMINO B: FAMILIAR PURO ---
                
                // 1. Delegamos al FamiliarService la creación/update de Persona y Familiar
                $familiarModel = $this->familiarService->crearOActualizarDesdeArray($datos);

                // 2. Vinculamos en tabla 'tiene_familiar'
                // Si ya existe, actualizamos 'activa' a true (reactivación) y la observación
                $alumno->familiares()->syncWithoutDetaching([
                    $familiarModel->id_familiar => [
                        'activa' => true,
                        'observaciones' => $observacionPivot
                    ]
                ]);
            }
        }
    }

    /**
     * Actualiza los datos básicos del alumno y su persona asociada.
     */
    public function actualizar(int $id, array $data): bool
    {
        $alumno = $this->repo->buscarPorId($id);
        
        if (!$alumno) {
            throw new \Exception("Alumno no encontrado.");
        }

        // 1. Actualizar Persona (Datos Personales)
        // USAMOS '??' PARA EVITAR EL ERROR DE 'UNDEFINED INDEX'
        // Si $data['dni'] no existe, usamos $alumno->persona->dni (el valor viejo)
        $alumno->persona->update([
            'dni' => $data['dni'] ?? $alumno->persona->dni,
            'nombre' => $data['nombre'] ?? $alumno->persona->nombre,
            'apellido' => $data['apellido'] ?? $alumno->persona->apellido,
            'fecha_nacimiento' => $data['fecha_nacimiento'] ?? $alumno->persona->fecha_nacimiento,
            'nacionalidad' => $data['nacionalidad'] ?? $alumno->persona->nacionalidad,
            'domicilio' => $data['domicilio'] ?? $alumno->persona->domicilio,
        ]);

        // 2. Actualizar Aula (Solo si vino el dato)
        if (!empty($data['aula'])) {
            if (!str_contains($data['aula'], '°')) {
                 throw new \Exception('Formato de aula inválido. Se espera "Curso°División".');
            }
            
            [$curso, $division] = explode('°', $data['aula']);
            
            $aula = \App\Models\Aula::where('curso', $curso)
                                    ->where('division', $division)
                                    ->first();
            
            if (!$aula) {
                throw new \Exception("No se encontró el aula {$data['aula']}.");
            }
            
            $alumno->fk_id_aula = $aula->id_aula;
        }

        // 3. Actualizar Datos del Alumno (Con lógica segura)
        $alumno->update([
            // Para booleanos, chequeamos si la clave existe antes de comparar
            'cud' => isset($data['cud']) ? (($data['cud'] === 'Sí') ? 1 : 0) : $alumno->cud,
            
            'inasistencias' => $data['inasistencias'] ?? $alumno->inasistencias,
            'cud' => ($data['cud'] ?? 'No') === 'Sí' ? 1 : 0,
            'situacion_socioeconomica' => $data['situacion_socioeconomica'] ?? null,
            'situacion_familiar' => $data['situacion_familiar'] ?? null,
            'situacion_medica' => $data['situacion_medica'] ?? null,
            'situacion_escolar' => $data['situacion_escolar'] ?? null,
            'actividades_extraescolares' => $data['actividades_extraescolares'] ?? null,
            'intervenciones_externas' => $data['intervenciones_externas'] ?? null,
            'antecedentes' => $data['antecedentes'] ?? null,
            'observaciones' => $data['observaciones'] ?? null,
        ]);

            // 3. Ejecutar Borrados Físicos (Hermanos Alumnos)
            // Rompemos el lazo (detach) en ambas direcciones por seguridad
            if (!empty($delHermanos)) {
                $alumno->hermanos()->detach($delHermanos);
                $alumno->esHermanoDe()->detach($delHermanos); 
            }

            // 4. Procesar Upserts (Crear o Actualizar relaciones)
            $this->procesarRelaciones($alumno, $listaFamiliares);

            return true;
    }

}