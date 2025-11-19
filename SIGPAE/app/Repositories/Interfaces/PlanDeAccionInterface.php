<?php
namespace App\Repositories\Interfaces;

use App\Models\PlanDeAccion;

interface PlanDeAccionRepositoryInterface
{
    public function obtenerTodos(): \Illuminate\Support\Collection;
    public function crear(array $data): PlanDeAccion;
    public function eliminar(int $id): bool;
    public function cambiarActivo(int $id): bool;
    public function buscarPorIdPersona(int $idPersona): ?PlanDeAccion;
    public function obtenerPlanesFiltrados(\Illuminate\Http\Request $request): \Illuminate\Contracts\Pagination\LengthAwarePaginator;
    public function obtenerAulasParaFiltro(): \Illuminate\Support\Collection;
    public function obtenerPorId(int $id): ?PlanDeAccion;
}
