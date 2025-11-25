<?php

namespace App\Services\Interfaces;

use App\Models\Alumno;

interface AlumnoServiceInterface
{
    public function listar(): \Illuminate\Support\Collection;

    public function crearAlumno(array $data): Alumno;

    public function crearAlumnoConFamiliares(array $alumnoData, array $familiaresTemp): Alumno;

    public function eliminar(int $id): bool;

    public function obtener(int $id): ?Alumno;

    public function obtenerParaEditar(int $id): ?Alumno;

    public function cambiarActivo(int $id): bool;

    public function filtrar(\Illuminate\Http\Request $request): \Illuminate\Support\Collection;

    public function obtenerCursos(): \Illuminate\Support\Collection;

    public function buscar(string $q): \Illuminate\Support\Collection;

    public function actualizar(int $id, array $data): bool;

}
