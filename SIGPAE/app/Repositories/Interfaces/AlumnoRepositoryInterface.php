<?php
namespace App\Repositories\Interfaces;

use App\Models\Alumno;

interface AlumnoRepositoryInterface
{
    public function obtenerTodos(): \Illuminate\Support\Collection;
    public function crear(array $data): Alumno;
    public function eliminar(int $id): bool;
    public function buscarPorId(int $id): ?Alumno;
    public function cambiarActivo(int $id): bool;
    public function buscarPorPersonaId(int $idPersona): ?Alumno;
    public function buscarParaEditar(int $id): ?Alumno;
    public function vincularHermanos(int $idAlumno, int $idHermano, ?string $observaciones): void;
}
