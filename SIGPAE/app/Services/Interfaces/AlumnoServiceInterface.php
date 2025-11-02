<?php

namespace App\Services\Interfaces;

use App\Models\Alumno;

interface AlumnoServiceInterface
{
    public function listar(): \Illuminate\Support\Collection;

    public function registrar(array $data): Alumno;

    public function eliminar(int $id): bool;

    public function obtener(int $id): ?Alumno;

    public function desactivar(int $id): bool;

}
