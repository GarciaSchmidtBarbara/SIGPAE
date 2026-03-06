<?php

namespace App\Repositories\Eloquent;

use App\Models\Aula;
use App\Repositories\Interfaces\AulaRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class AulaRepository implements AulaRepositoryInterface
{
    public function obtenerCursos(): Collection
    {
        // Retorna todos los registros de la tabla aulas
        return Aula::all();
    }

    public function buscarPorCursoYDivision(string $curso, string $division): ?Aula
    {
        // Busca un aula específica por sus dos columnas clave
        return Aula::where('curso', $curso)
                   ->where('division', $division)
                   ->first();
    }

    public function obtenerTodas(): Collection
    {
        return Aula::all();
    }
}