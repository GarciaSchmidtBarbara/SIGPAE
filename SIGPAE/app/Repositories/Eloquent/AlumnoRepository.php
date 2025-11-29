<?php
namespace App\Repositories\Eloquent;

use App\Models\Alumno;
use App\Models\Aula;
use App\Repositories\Interfaces\AlumnoRepositoryInterface;

use \Illuminate\Support\Collection;

//Define cómo se obtienen los datos (ORM, query builder, SQL, etc.)
//Es la capa que sí toca los modelos Eloquent (Alumno, Persona, etc.)
//Aquí es donde va la consulta con los join y orderBy 


class AlumnoRepository implements AlumnoRepositoryInterface

{
    public function obtenerTodos(): \Illuminate\Support\Collection
    {
        return Alumno::with(['persona', 'aula'])
            ->whereHas('persona') // asegura que tenga relación
            ->get()
            ->sortByDesc(fn($a) => $a->persona->activo) // primero activos
            ->sortBy(fn($a) => $a->persona->apellido)   // luego orden alfabético
            ->values(); // reindexa los resultados
    }

    //filtros consulta BD OK
    public function filtrar(array $filters): Collection 
    {
        $query = Alumno::with(['persona', 'aula']);

        if (!empty($filters['nombre'])) {
            $nombre = $filters ['nombre'];
            $query->whereHas('persona', fn($q) =>
                $q->whereRaw("LOWER(unaccent(nombre::text)) LIKE ?", ["%{$nombre}%"])
            );
        }

        if (!empty($filters['apellido'])) {
            $apellido = $filters['apellido'];
            $query->whereHas('persona', fn($q) =>
                $q->whereRaw("LOWER(unaccent(apellido)) LIKE ?", ["%{$apellido}%"])
            );
        }

        if (!empty($filters['documento'])) {
            $query->whereHas('persona', fn($q) =>
                $q->where('dni', 'like', '%' . $filters['documento'] . '%')
            );
        }

        if (!empty($filters['aula'])) {
            [$curso, $division] = explode('°', $filters['aula']);
            $query->whereHas('aula', fn($q) =>
                $q->where('curso', $curso)->where('division', $division)
            );
        }
        
        if (isset($filters['estado'])) {
            if ($filters['estado'] === 'activos') {
                $query->whereHas('persona', fn($q) => $q->where('activo', true));
            } elseif ($filters['estado'] === 'inactivos') {
                $query->whereHas('persona', fn($q) => $q->where('activo', false));
            }
        }

        return $query->get();
    }

    //ok
    public function obtenerCursos(): Collection{
        return Aula::all();
    }

    public function crear(array $data): Alumno
    {
        return Alumno::create($data);
    }

    public function eliminar(int $id): bool
    {
        $alumno = Alumno::find($id);
        return $alumno ? $alumno->delete() : false;
    }

    public function obtenerPorPersonaId(int $idPersona): ?Alumno
    {
        return Alumno::where('fk_id_persona', $idPersona)->first();
    }

    //ok
    public function obtenerPorId(int $id): ?Alumno
    {
        return Alumno::with(['persona', 'aula'])->find($id);
    }

    // creo este metodo para no sobrecargar el buscarPorId, sino ya no seria tan reutilizablae
    // este nuevo metodo busca todo lo necesario para cargar los datos del alumno en asistente (sesion) OK
    public function obtenerParaEditar(int $id): ?Alumno
    {
        return Alumno::with([
            'persona', 
            'aula', 
            
            // Familiares Puros (Filtrados)
            'familiares' => function ($query) {
                $query->wherePivot('activa', true);
            },
            'familiares.persona',
            
            // Hermanos Lado A (AHORA FILTRADOS)
            'hermanos' => function ($query) {
                $query->wherePivot('activa', true);
            },
            'hermanos.persona',
            'hermanos.aula',
            
            // Hermanos Lado B (AHORA FILTRADOS)
            'esHermanoDe' => function ($query) {
                $query->wherePivot('activa', true);
            },
            'esHermanoDe.persona', 
            'esHermanoDe.aula'

        ])->find($id);
    }
    
    //metodo para cambiar de activo a inactivo  OK
    public function cambiarActivo(int $id): bool
    {
        $alumno = Alumno::with('persona')->find($id);
        if ($alumno && $alumno->persona) {
            $alumno->persona->activo = !$alumno->persona->activo;
            return $alumno->persona->save();
        }
        return false;
    }

    public function vincularHermanos(int $idAlumno, int $idHermano, ?string $observaciones): void
    {
        // Buscamos al alumno principal para acceder a su relación
        $alumno = $this->buscarPorId($idAlumno);

        // 1. Verificar existencia en la tabla pivote
        $existe = $alumno->hermanos()
                         ->where('es_hermano_de.fk_id_alumno_hermano', $idHermano)
                         ->exists();

        if ($existe) {
            // UPDATE: Si ya existe, actualizamos observación y reactivamos
            $alumno->hermanos()->updateExistingPivot($idHermano, [
                'observaciones' => $observaciones,
                'activa' => true
            ]);
        } else {
            // INSERT: Si no existe, creamos
            $alumno->hermanos()->attach($idHermano, [
                'observaciones' => $observaciones,
                'activa' => true
            ]);
        }

        // 2. Hacemos la inversa (B -> A) sin tocar observaciones
        $hermano = $this->buscarPorId($idHermano);
        
        $existeInverso = $hermano->hermanos()
                                 ->where('es_hermano_de.fk_id_alumno_hermano', $idAlumno)
                                 ->exists();

        if ($existeInverso) {
            $hermano->hermanos()->updateExistingPivot($idAlumno, ['activa' => true]);
        } else {
            $hermano->hermanos()->attach($idAlumno, ['activa' => true]);
        }
    }

}
