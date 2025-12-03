<?php

namespace App\Services\Interfaces;

use App\Models\PlanDeAccion;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

interface PlanDeAccionServiceInterface
{
    public function cambiarActivo(int $id): bool;
    public function crear(array $data): PlanDeAccion;
    public function eliminar(int $id): bool;
    public function buscarPorId(int $id): ?PlanDeAccion;
    public function filtrar(Request $request): Collection;
    public function obtenerAulas(): Collection;
    public function obtenerTipos(): Collection;
    public function datosParaFormulario(?int $id = null): array;

}
