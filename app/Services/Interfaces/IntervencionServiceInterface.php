<?php

namespace App\Services\Interfaces;

use App\Models\Intervencion;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;

interface IntervencionServiceInterface
{
    public function crear(array $data): Intervencion;
    public function actualizar(int $id, array $data): ?Intervencion;
    public function eliminar(int $id): bool;
    public function cambiarActivo(int $id): bool;
    public function obtenerTipos(): Collection;
    public function obtenerAulas(): Collection;
    public function buscarPorId(int $id): ?Intervencion;
    public function formatearParaVista(Collection $intervenciones): Collection;
    public function filtrar(Request $request): Collection;
    public function guardarOtrosAsistentes(Intervencion $intervencion, array $filas);
    public function datosParaFormulario(?int $id = null): array;
}