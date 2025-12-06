<?php

namespace App\Repositories\Interfaces;

use App\Models\Aula;
use Illuminate\Database\Eloquent\Collection;

interface AulaRepositoryInterface
{
    public function obtenerCursos(): Collection;
    
    public function buscarPorCursoYDivision(string $curso, string $division): ?Aula;
}