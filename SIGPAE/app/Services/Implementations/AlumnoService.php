<?php
namespace App\Services\Implementations;
// IMPORTS
// Alumno
use App\Repositories\Interfaces\AlumnoRepositoryInterface;
use App\Services\Interfaces\AlumnoServiceInterface;
// Models
use App\Models\Alumno;
use App\Models\Aula;
use App\Models\Persona;
// Requests
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
        try {
            $fecha = \DateTime::createFromFormat('Y-m-d', $data['fecha_nacimiento'] ?? '');
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
    public function crearAlumnoConFamiliares(array $alumnoData, array $familiaresTemp): Alumno
    {
        \DB::beginTransaction();
        try {
            $alumno = $this->crearAlumno($alumnoData); //Para crear la persona+alumno

            if (!empty($familiaresTemp)) {
                $famSrv = app(\App\Services\Interfaces\FamiliarServiceInterface::class);
                $personaSrv = app(\App\Services\Interfaces\PersonaServiceInterface::class);

                $alumnoModel = app(\App\Models\Alumno::class);

                foreach ($familiaresTemp as $f) {
                    $parentesco = strtoupper((string)($f['parentesco'] ?? ''));
                    $map = [ 'padre'=>'PADRE','madre'=>'MADRE','hermano'=>'HERMANO','tutor'=>'TUTOR','otro'=>'OTRO' ];
                    if (!in_array($parentesco, ['PADRE','MADRE','HERMANO','TUTOR','OTRO'])) {
                        $parentesco = $map[strtolower($parentesco)] ?? 'OTRO';
                    }
                    
                    if ($parentesco === 'HERMANO') {
                        if (empty($f['fk_id_persona'])) {
                            throw new \Exception('Se seleccionó "Hermano" pero no se proporcionó un ID de persona (fk_id_persona).');
                        }
                        $hermano = $alumnoModel->where('fk_id_persona', (int)$f['fk_id_persona'])->first();
                        
                        if (!$hermano) {
                            throw new \Exception('El ID de persona proporcionado para el hermano no corresponde a un alumno existente.');
                        }
                        $alumno->hermanos()->attach($hermano->id_alumno);

                    } else { //este else es el que sta en rojo

                        $payloadFamiliar = [
                            'parentesco'        => $parentesco,
                            'otro_parentesco'   => $f['otro_parentesco'] ?? null,
                            'telefono_personal' => $f['telefono_personal'] ?? null,
                            'telefono_laboral'  => $f['telefono_laboral'] ?? null,
                            'lugar_de_trabajo'  => $f['lugar_de_trabajo'] ?? null,
                            'observaciones'     => $f['observaciones'] ?? null,
                        ];

                        if (!empty($f['fk_id_persona'])) {
                            // Esto es si el familiar (ej. un padre) ya existía en la BBDD
                            $payloadFamiliar['fk_id_persona'] = (int)$f['fk_id_persona'];
                        } else {
                            // Creamos una NUEVA Persona para este familiar
                            $personaPayload = [
                                'nombre'            => $f['nombre'] ?? null,
                                'apellido'          => $f['apellido'] ?? null,
                                'dni'               => $f['dni'] ?? null,
                                'fecha_nacimiento'  => $f['fecha_nacimiento'] ?? null,
                                'domicilio'         => $f['domicilio'] ?? null,
                                'nacionalidad'      => $f['nacionalidad'] ?? null,
                            ];
                            $persona = $personaSrv->createPersona($personaPayload);
                            $payloadFamiliar['fk_id_persona'] = $persona->id_persona;
                        }

                        // Creamos el registro en la tabla 'familiares'
                        $familiar = $famSrv->createFamiliar($payloadFamiliar);
                        // Usamos la relación 'familiares()' (tabla 'tiene_familiar')
                        $alumno->familiares()->attach($familiar->id_familiar);
                    }
                }
            }

            \DB::commit();
            return $alumno->load(['persona','aula','familiares.persona', 'hermanos.persona']);
        } catch (\Throwable $t) {
            \DB::rollBack();
            throw $t;
        }
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

    public function actualizar(int $id, array $data): bool
    {
        $alumno = $this->repo->buscarPorId($id);
        if (!$alumno) {
            throw new \Exception('Alumno no encontrado.');
        }

        $persona = $alumno->persona;
        $persona->update([
            'dni' => $data['dni'] ?? $persona->dni,
            'nombre' => $data['nombre'] ?? $persona->nombre,
            'apellido' => $data['apellido'] ?? $persona->apellido,
            'fecha_nacimiento' => $data['fecha_nacimiento'] ?? $persona->fecha_nacimiento,
            'nacionalidad' => $data['nacionalidad'] ?? $persona->nacionalidad,
        ]);

        // actualizar aula
        if (!empty($data['aula']) && str_contains($data['aula'], '°')) {
            [$curso, $division] = explode('°', $data['aula']);
            $aula = \App\Models\Aula::where('curso', $curso)
                ->where('division', $division)
                ->first();
            if ($aula) {
                $alumno->fk_id_aula = $aula->id_aula;
            }
        }

        $alumno->fill([
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

        return $alumno->save();
    }

}