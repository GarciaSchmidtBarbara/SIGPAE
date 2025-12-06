<?php

namespace App\Services\Interfaces;

use Illuminate\Support\Collection;

interface AulaServiceInterface
{
    public function obtenerCursos(): Collection;

    public function resolverIdPorDescripcion(string $descripcion): int;
}