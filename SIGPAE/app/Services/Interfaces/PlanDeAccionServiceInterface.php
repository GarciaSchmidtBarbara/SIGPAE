<?php

namespace App\Services\Interfaces;

use App\Models\PlanDeAccion;

interface PlanDeAccionServiceInterface
{
    public function listar(): \Illuminate\Support\Collection;
    public function crear(array $data): PlanDeAccion;
    public function eliminar(int $id): bool;
    public function obtener(int $id): ?PlanDeAccion;
    public function cambiarActivo(int $id): bool;
    public function buscar(string $q): \Illuminate\Support\Collection;
    public function obtenerPlanesParaPrincipal(\Illuminate\Http\Request $request): array;
    public function obtenerAulasParaFiltro(): \Illuminate\Support\Collection;
    public function datosParaFormulario(?int $id = null): array;

}
