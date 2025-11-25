<?php
namespace App\Repositories\Interfaces;

use App\Models\PlanDeAccion;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;

interface PlanDeAccionRepositoryInterface
{
    public function crear(array $data): PlanDeAccion;
    public function eliminar(int $id): bool;
    public function cambiarActivo(int $id): bool;
    public function filtrar(array $filtros): \Illuminate\Database\Eloquent\Builder;
    public function buscarPorIdPersona(int $idPersona): ?PlanDeAccion;
    public function obtenerPlanesFiltrados(Request $request): Collection;
    public function obtenerAulasParaFiltro(): Collection;
    public function obtenerPorId(int $id): ?PlanDeAccion;
    public function obtenerTodos(): Collection;
    public function buscarPorIdConRelaciones(int $id);
    
}
