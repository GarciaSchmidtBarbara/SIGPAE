<?php

namespace App\Services\Interfaces;

use App\Models\PlanDeAccion;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

interface PlanDeAccionServiceInterface
{
    public function crear(array $data): PlanDeAccion;
    public function actualizar(int $id, array $data): ?PlanDeAccion;
    public function eliminar(int $id): bool;
    public function obtenerEliminados(): Collection;
    public function restaurar(int $id): bool;
    public function eliminarDefinitivo(int $id): bool;
    public function buscarPorId(int $id): ?PlanDeAccion;
    public function filtrar(Request $request): Collection;
    public function obtenerTodos(): Collection;
    public function obtenerTodosConRelaciones(): Collection;
    public function obtenerAulas(): Collection;
    public function obtenerTipos(): Collection;
    public function datosParaFormulario(?int $id = null): array;
    public function crearEvaluacion(int $idPlan, array $data): bool;
    public function obtenerParaEvaluacion(int $id): PlanDeAccion;
    public function guardarEvaluacion(int $id, array $data): void;


}
