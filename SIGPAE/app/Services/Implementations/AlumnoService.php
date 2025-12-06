<?php
namespace App\Services\Implementations;

use App\Repositories\Interfaces\AlumnoRepositoryInterface;
use App\Services\Interfaces\AlumnoServiceInterface;
// Se importa otros servicios para delegar tareas específicas
use App\Services\Interfaces\PersonaServiceInterface;
use App\Services\Interfaces\FamiliarServiceInterface;
use App\Services\Interfaces\AulaServiceInterface;
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
    protected PersonaServiceInterface $personaService;
    protected FamiliarServiceInterface $familiarService;
    protected AulaServiceInterface $aulaService;

    public function __construct(AlumnoRepositoryInterface $repo, PersonaServiceInterface $personaService,
        FamiliarServiceInterface $familiarService, AulaServiceInterface $aulaService)
    {
        $this->repo = $repo;
        $this->personaService = $personaService;
        $this->familiarService = $familiarService;
        $this->aulaService = $aulaService;
    }

    public function listar(): \Illuminate\Support\Collection
    {
        return $this->repo->obtenerTodos();
    }

    public function crearAlumno(array $data): Alumno
    {
        return DB::transaction(function () use ($data) {
            try {
                $persona = $this->personaService->createPersona([
                    'dni' => $data['dni'],
                    'nombre' => $data['nombre'],
                    'apellido' => $data['apellido'],
                    'fecha_nacimiento' => $data['fecha_nacimiento'],
                    'nacionalidad' => $data['nacionalidad'],
                    'domicilio' => $data['domicilio'] ?? null,
                    'activo' => true,
                ]);
                
                $aulaId = $this->aulaService->buscarAulaPorDescripcion($data['aula']);
                
                $datosParaGuardar = [
                    'fk_id_persona' => $persona->id_persona,
                    'fk_id_aula' => $aulaId,
                    'cud' => ($data['cud'] ?? 'No') === 'Sí' ? 1 : 0,
                    'inasistencias' => $data['inasistencias'] ?? null,
                    'situacion_socioeconomica' => $data['situacion_socioeconomica'] ?? null,
                    'situacion_familiar' => $data['situacion_familiar'] ?? null,
                    'situacion_medica' => $data['situacion_medica'] ?? null,
                    'situacion_escolar' => $data['situacion_escolar'] ?? null,
                    'actividades_extraescolares' => $data['actividades_extraescolares'] ?? null,
                    'intervenciones_externas' => $data['intervenciones_externas'] ?? null,
                    'antecedentes' => $data['antecedentes'] ?? null,
                    'observaciones' => $data['observaciones'] ?? null,
                ];

                $alumno = $this->repo->crear($datosParaGuardar);
                
                if (!$alumno->exists) {
                    throw new \Exception('El alumno no se guardó correctamente');
                }

                return $alumno;
            } catch (\Throwable $e) {
                // El catch ahora maneja la excepción, y la transacción se revertirá.
                \Log::error('Error al crear alumno: '.$e->getMessage(), ['data' => $data]);
                throw new \Exception('Ocurrió un error al crear el alumno. '.$e->getMessage());
            }
        });
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
        // Si está vacío, no consultamos a la bbdd
        if (trim($q) === '') {
            return collect();
        }

        return $this->repo->buscarPorTermino($q);
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

    public function filtrar(Request $request): \Illuminate\Support\Collection
    {
        $criterios = [
            'dni'    => $request->get('documento'), // Mapeamos input 'documento' a criterio 'dni'
            'estado' => $request->get('estado', 'activos'),
        ];

        // Normalización de textos
        if ($request->filled('nombre')) {
            $criterios['nombre'] = $this->normalizarTexto($request->nombre);
        }
        if ($request->filled('apellido')) {
            $criterios['apellido'] = $this->normalizarTexto($request->apellido);
        }

        // Lógica de Aula (Separar string)
        if ($request->filled('aula') && str_contains($request->aula, '°')) {
            [$curso, $division] = explode('°', $request->aula);
            $criterios['curso'] = $curso;
            $criterios['division'] = $division;
        }

        return $this->repo->filtrar($criterios);
    }

    private function normalizarTexto(string $texto): string
    {
        return strtolower(strtr(iconv('UTF-8', 'ASCII//TRANSLIT', $texto), "´`^~¨", "     "));
    }

    //Actualiza los datos básicos del alumno y su persona asociada.
    public function actualizar(int $id, array $data, array $listaFamiliares, array $familiaresAEliminar, array $hermanosAEliminar): bool
    {
        return DB::transaction(function () use ($id, $data, $listaFamiliares, $familiaresAEliminar, $hermanosAEliminar) {
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
        });
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

                // aca si utizo syncWithoutDetaching, porque sino requeriria logica adicional en el repo,
                // y no vale la pena por ser un caso tan puntual
                $alumno->familiares()->syncWithoutDetaching([
                    $familiarModel->id_familiar => [
                        'activa' => true,
                        'observaciones' => $observacionPivot
                    ]
                ]);
            }
        }
    }

}