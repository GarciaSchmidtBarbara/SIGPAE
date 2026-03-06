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
    public function buscarPorTermino(string $termino): \Illuminate\Support\Collection;
    public function filtrar(array $criterios): \Illuminate\Support\Collection;
    public function buscarPorAula(int $aulaId): \Illuminate\Support\Collection;
    public function actualizar(int $id, array $data): bool;
    public function desvincularHermanos(int $idAlumno, array $idsHermanos): void;
    public function desactivarFamiliares(int $idAlumno, array $idsFamiliares): void;
    public function vincularFamiliar(int $idAlumno, int $idFamiliar, array $pivotData): void;
}
