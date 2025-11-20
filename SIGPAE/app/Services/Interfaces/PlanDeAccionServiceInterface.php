<?php

namespace App\Services\Interfaces;

use App\Models\PlanDeAccion;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

interface PlanDeAccionServiceInterface
{
    public function listar(): Collection;
    public function cambiarActivo(int $id): bool;
    public function crear(array $data): PlanDeAccion;
    public function eliminar(int $id): bool;
    public function obtener(int $id): ?PlanDeAccion;
    public function buscar(string $q): Collection;
    public function filtrar(Request $request): Collection;
    public function obtenerAulasParaFiltro(): Collection;
    public function obtenerTipos(): Collection;
    public function datosParaFormulario(?int $id = null): array;

}
