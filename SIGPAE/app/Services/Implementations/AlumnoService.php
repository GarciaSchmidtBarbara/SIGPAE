<?php
namespace App\Services\Implementations;

use App\Models\Alumno;
use App\Services\Interfaces\AlumnoServiceInterface;
use App\Repositories\Interfaces\AlumnoRepositoryInterface;

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

    public function registrar(array $data): Alumno
    {
        // Acá podrías agregar lógica institucional, como validaciones internas o asignaciones automáticas
        return $this->repo->crear($data);
    }

    public function eliminar(int $id): bool
    {
        return $this->repo->eliminar($id);
    }

    public function obtener(int $id): ?Alumno
    {
        return $this->repo->buscarPorId($id);
    }

    public function desactivar(int $id): bool
    {
        return $this->repo->desactivar($id);
    }


}
