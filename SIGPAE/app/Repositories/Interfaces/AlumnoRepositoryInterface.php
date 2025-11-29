<?php
namespace App\Repositories\Interfaces;

use App\Models\Alumno;
use Illuminate\Support\Collection;

interface AlumnoRepositoryInterface
{
    public function obtenerTodos(): Collection;
    public function filtrar(array $filters): Collection; //OK
    public function crear(array $data): Alumno;
    public function eliminar(int $id): bool;
    public function obtenerPorId(int $id): ?Alumno; //OK
    public function cambiarActivo(int $id): bool; //OK
    public function obtenerPorPersonaId(int $idPersona): ?Alumno;
    public function obtenerParaEditar(int $id): ?Alumno; //OK
    public function obtenerCursos(): Collection; //OK
    public function vincularHermanos(int $idAlumno, int $idHermano, ?string $observaciones): void;
}
