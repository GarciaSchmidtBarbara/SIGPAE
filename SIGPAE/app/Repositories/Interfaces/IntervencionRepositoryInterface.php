<?php

namespace App\Repositories\Interfaces;

use Illuminate\Http\Request;
use App\Models\Intervencion;
use Illuminate\Support\Collection;

interface IntervencionRepositoryInterface
{
    public function buscarPorId (int $id): ?Intervencion;
    public function buscarPorIdConRelaciones(int $id): ?Intervencion;
    public function filtrar (Request $request): Collection;
    public function crear (array $data): Intervencion;
    public function actualizar (int $id, array $data): ?Intervencion;
    public function eliminar (int $id): bool;
    public function cambiarActivo (int $id): bool;
    public function obtenerAulas(): Collection;
    public function guardarOtrosAsistentes(Intervencion $intervencion, array $filas);
}