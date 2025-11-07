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
            ->sortByDesc(fn($a) => $a->persona->activo) // primero activos
            ->sortBy(fn($a) => $a->persona->apellido)   // luego orden alfabético
            ->values(); // reindexa los resultados
    }

    public function crear(array $data): Alumno
    {
        return Alumno::crearAlumno($data);
    }

    public function eliminar(int $id): bool
    {
        $alumno = Alumno::find($id);
        return $alumno ? $alumno->delete() : false;
    }

    public function buscarPorId(int $id): ?Alumno
    {
        return Alumno::with(['persona', 'aula'])->find($id);
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

}
