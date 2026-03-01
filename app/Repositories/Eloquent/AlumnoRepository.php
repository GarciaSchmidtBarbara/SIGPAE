<?php
namespace App\Repositories\Eloquent;

use App\Models\Alumno;
use App\Repositories\Interfaces\AlumnoRepositoryInterface;

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
            ->sort(function($a, $b) {
                if ($a->persona->activo !== $b->persona->activo) {
                    return $b->persona->activo <=> $a->persona->activo; // primero activos
                }
                return strcmp($a->persona->apellido, $b->persona->apellido); // luego alfabéticamente
            })
            ->values();
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

    public function buscarPorPersonaId(int $idPersona): ?Alumno
    {
        return Alumno::where('fk_id_persona', $idPersona)->first();
    }

    public function buscarPorId(int $id): ?Alumno
    {
        return Alumno::with(['persona', 'aula'])->find($id);
    }

    // creo este metodo para no sobrecargar el buscarPorId, sino ya no seria tan reutilizablae
    // este nuevo metodo busca todo lo necesario para cargar los datos del alumno en asistente (sesion)
    public function buscarParaEditar(int $id): ?Alumno
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

    //metodo para cambiar de activo a inactivo
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

    public function buscarPorTermino(string $termino, ?int $excludeId = null): \Illuminate\Support\Collection
    {
        // Preparamos el string para SQL LIKE
        // esto se hace para evitar inyecciones SQL
        // y no en el servicio porque es algo propio de postgres
        $like = '%' . str_replace('%', '', $termino) . '%';

        return Alumno::with(['persona', 'aula'])
            ->whereHas('persona', function ($sub) use ($like) {
                $sub->where('dni', 'like', $like)
                    ->orWhere('nombre', 'ilike', $like) // ilike es para Postgres (insensible a mayúsculas)
                    ->orWhere('apellido', 'ilike', $like);
            })
            ->when($excludeId, function ($query, $excludeId) {
                // Filtramos para que no traiga al alumno actual
                return $query->where('id_alumno', '!=', $excludeId);
            })
            ->limit(10)
            ->get();
    }

    public function buscarPorAula(int $aulaId): \Illuminate\Support\Collection
    {
        return Alumno::with(['persona', 'aula'])
            ->where('fk_id_aula', $aulaId)
            ->whereHas('persona', fn($q) => $q->where('activo', true))
            ->get();
    }

    public function filtrar(array $criterios): \Illuminate\Support\Collection
    {
        $query = Alumno::with('persona', 'aula');

        // 1. Filtro por Nombre (Normalizado)
        if (!empty($criterios['nombre'])) {
            $nombre = $criterios['nombre'];
            $query->whereHas('persona', fn($q) =>
                $q->whereRaw("LOWER(unaccent(nombre::text)) LIKE ?", ["%{$nombre}%"])
            );
        }

        // 2. Filtro por Apellido
        if (!empty($criterios['apellido'])) {
            $apellido = $criterios['apellido'];
            $query->whereHas('persona', fn($q) =>
                $q->whereRaw("LOWER(unaccent(apellido)) LIKE ?", ["%{$apellido}%"])
            );
        }

        // 3. Filtro por Documento
        if (!empty($criterios['dni'])) {
            $query->whereHas('persona', fn($q) =>
                $q->where('dni', 'like', '%' . $criterios['dni'] . '%')
            );
        }

        // 4. Filtro por Aula
        if (!empty($criterios['curso']) && !empty($criterios['division'])) {
            $query->whereHas('aula', fn($q) =>
                $q->where('curso', $criterios['curso'])
                  ->where('division', $criterios['division'])
            );
        }

        // 5. Filtro por Estado
        $estado = $criterios['estado'] ?? 'activos';
        if ($estado === 'activos') {
            $query->whereHas('persona', fn($q) => $q->where('activo', true));
        } elseif ($estado === 'inactivos') {
            $query->whereHas('persona', fn($q) => $q->where('activo', false));
        }

        // 6. Ejecución y Ordenamiento
        // (Mantenemos tu lógica de ordenamiento en memoria por ahora para no complicar el SQL con JOINS)
        return $query->get()->sort(function ($a, $b) {
            $activoA = data_get($a, 'persona.activo') ? 1 : 0;
            $activoB = data_get($b, 'persona.activo') ? 1 : 0;

            if ($activoA !== $activoB) {
                return $activoA > $activoB ? -1 : 1;
            }

            $nombreA = mb_strtolower(data_get($a, 'persona.nombre', ''));
            $nombreB = mb_strtolower(data_get($b, 'persona.nombre', ''));

            return $nombreA <=> $nombreB;
        })->values();
    }

}
