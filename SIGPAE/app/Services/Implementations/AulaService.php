<?php

namespace App\Services\Implementations;

use App\Services\Interfaces\AulaServiceInterface;
use App\Repositories\Interfaces\AulaRepositoryInterface;
use Exception;
use Illuminate\Support\Collection;

class AulaService implements AulaServiceInterface
{
    protected $aulaRepository;

    public function __construct(AulaRepositoryInterface $aulaRepository)
    {
        $this->aulaRepository = $aulaRepository;
    }

    public function obtenerCursos(): Collection
    {
        // Asumimos que tu modelo tiene un accessor 'descripcion'
        return $this->aulaRepository->getAll()
                    ->map(fn($a) => $a->descripcion) 
                    ->unique();
    }

    public function resolverIdPorDescripcion(string $descripcion): int
    {
        if (!str_contains($descripcion, '°')) {
            throw new Exception('Formato de aula inválido. Ejemplo esperado: "3°A".');
        }

        [$curso, $division] = explode('°', $descripcion);

        $aula = $this->aulaRepository->findByCursoDivision($curso, $division);

        if (!$aula) {
            throw new Exception("No se encontró el aula: $descripcion");
        }

        return $aula->id_aula;
    }
}