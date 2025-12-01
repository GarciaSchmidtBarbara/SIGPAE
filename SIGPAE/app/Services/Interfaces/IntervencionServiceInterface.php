<?php

namespace App\Services\Interfaces;

use App\Models\Intervencion;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;

interface IntervencionServiceInterface
{
    public function crear(array $data);
    public function editar(int $id, array $data): bool;
    public function eliminar(int $id): bool;
    public function cambiarActivo(int $id): bool;
    public function buscarPorId(int $id);
    public function obtenerTipos(): Collection;
    public function obtenerAulas(): Collection;
    public function filtrar(Request $request): Collection;
}