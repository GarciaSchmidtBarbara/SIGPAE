<?php

namespace App\Services\Interfaces;

use App\Models\Documento;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

interface DocumentoServiceInterface
{
    public function listar(Request $request): Collection;
    public function listarParaAlumno(int $idAlumno): array;
    public function listarParaIntervencion(int $idIntervencion): array;
    public function subir(array $data, UploadedFile $archivo, int $idProfesional): Documento;
    public function descargar(int $id): Documento;
    public function eliminar(int $id): bool;
    public function eliminarVarios(array $ids): void;
    public function buscarEntidadPorContexto(string $contexto, string $termino): array;
}
