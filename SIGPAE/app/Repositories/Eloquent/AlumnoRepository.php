<?php
namespace App\Repositories\Eloquent;

use App\Models\Alumno;
use App\Repositories\Interfaces\AlumnoRepositoryInterface;


class AlumnoRepository implements AlumnoRepositoryInterface

{
    public function obtenerTodos(): \Illuminate\Support\Collection
    {
        return Alumno::with(['persona', 'aula'])->get();
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

    public function desactivar(int $id): bool
    {
        $alumno = Alumno::with('persona')->find($id);
        if ($alumno && $alumno->persona) {
            $alumno->persona->activo = false;
            return $alumno->persona->save();
        }
        return false;
    }

}
