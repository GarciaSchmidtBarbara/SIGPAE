<?php
namespace App\Services\Implementations;

use App\Repositories\Interfaces\AlumnoRepositoryInterface;
use App\Services\Interfaces\AlumnoServiceInterface;
use App\Models\Alumno;
use App\Models\Aula;

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
        //validaciones
        $fecha = \DateTime::createFromFormat('d/m/Y', $data['fecha_nacimiento']);
        $data['fecha_nacimiento'] = $fecha ? $fecha->format('Y-m-d') : null;
    
        //Crear la persona asociada
        $persona = \App\Models\Persona::create([
            'dni' => $data['dni'],
            'nombre' => $data['nombre'],
            'apellido' => $data['apellido'],
            'fecha_nacimiento' => $data['fecha_nacimiento'],
            'nacionalidad' => $data['nacionalidad'],
            'activo' => true,
        ]);
        if (!$persona || !$persona->id_persona) {
            throw new \Exception('La persona no se creó correctamente');
        }

        // Buscar el aula
        if (str_contains($data['aula'], '°')) {
            [$curso, $division] = explode('°', $data['aula']);
            $aula = Aula::where('curso', $curso)
                        ->where('division', $division)
                        ->first();
        } else {
            throw new \Exception('Formato de aula inválido');
        }
        if (!$aula) {
            throw new \Exception('No se encontró el aula con la descripción: ' . $data['aula']);
        }
        
        $cud = $data['cud'] === 'Sí' ? 1 : 0;
        //Crear el alumno con los datos restantes
        $alumno = new \App\Models\Alumno([
            'fk_persona' => $persona->id_persona,
            'fk_aula' => $aula?->id_aula,
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


}
