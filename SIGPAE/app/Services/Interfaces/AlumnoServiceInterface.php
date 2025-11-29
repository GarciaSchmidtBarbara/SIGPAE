<?php

namespace App\Services\Interfaces;

use App\Models\Alumno;
use Illuminate\Support\Collection;

interface AlumnoServiceInterface
{
    public function listar(): \Illuminate\Support\Collection;

    public function crearAlumno(array $data): Alumno;

    public function crearAlumnoConFamiliares(array $alumnoData, array $familiaresTemp): Alumno;

    public function eliminar(int $id): bool;

    public function obtener(int $id): ?Alumno;

    public function obtenerParaEditar(int $id): ?Alumno; //OK

    public function cambiarActivo(int $id): bool; //OK

    public function filtrar(\Illuminate\Http\Request $request): Collection; //OK

    public function obtenerCursos(): Collection; //OK

    public function buscar(string $q): Collection;

    public function actualizar(int $id, array $data, array $listaFamiliares, 
    array $familiaresAEliminar, array $hermanosAEliminar): bool;

}
