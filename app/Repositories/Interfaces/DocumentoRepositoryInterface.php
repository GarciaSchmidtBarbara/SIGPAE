<?php

namespace App\Repositories\Interfaces;

use App\Models\Documento;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

interface DocumentoRepositoryInterface
{
    public function buscarPorId(int $id): ?Documento;
    public function filtrar(Request $request): Collection;
    public function todos(): Collection;
    public function crear(array $data): Documento;
    public function eliminar(int $id): bool;
    public function buscarPorAlumno(int $idAlumno): Collection;
    public function buscarPorIntervencion(int $idIntervencion): Collection;
}
