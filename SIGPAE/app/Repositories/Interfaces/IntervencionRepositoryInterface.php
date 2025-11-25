<?php

namespace App\Repositories\Interfaces;

use Illuminate\Http\Request;
use App\Models\Intervencion;
use Illuminate\Support\Collection;

interface IntervencionRepositoryInterface
{
    public function obtenerTodos ();
    public function buscarPorId (int $id): ?Intervencion;
    public function filtrar (array $filters = []);
    public function crear (array $data): Intervencion;
    public function editar (int $id, array $data): bool;
    public function eliminar (int $id): bool;
    public function cambiarActivo (int $id): bool;
    public function obtenerAulasParaFiltro(): Collection;
}