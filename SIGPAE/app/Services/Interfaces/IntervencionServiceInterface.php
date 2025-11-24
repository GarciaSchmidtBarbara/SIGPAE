<?php

namespace App\Services\Interfaces;

use App\Models\Intervencion;
use Illuminate\Support\Collection;

interface IntervencionServiceInterface
{
    public function crear(array $data);
    public function actualizar(int $id, array $data): bool;
    public function eliminar(int $id): bool;
    public function cambiarActivo(int $id): bool;
    public function obtenerTodos();
    public function buscar(int $id);
    public function obtenerTipos(): Collection;
    public function obtenerAulasParaFiltro(): Collection;
    public function obtenerIntervenciones(array $filters = []);
}