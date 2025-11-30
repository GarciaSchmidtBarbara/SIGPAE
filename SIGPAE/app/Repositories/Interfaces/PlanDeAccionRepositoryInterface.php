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
    public function buscarPorIdPersona(int $idPersona): ?PlanDeAccion;
    public function filtrar(Request $request): Collection;
    public function obtenerAulas(): Collection;
    public function obtenerModelosAulas():Collection; //devuelve modelos Eloquent
    public function buscarPorId(int $id): ?PlanDeAccion;
    public function buscarPorIdConRelaciones(int $id);
    
}
