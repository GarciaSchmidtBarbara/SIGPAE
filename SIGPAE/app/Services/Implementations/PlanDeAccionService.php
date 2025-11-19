<?php

namespace App\Services\Implementations;

use App\Repositories\PlanDeAccionRepository;
use App\Services\Interfaces\PlanDeAccionServiceInterface;
use App\Repositories\Interfaces\PlanDeAccionRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Models\PlanDeAccion;

class PlanDeAccionService implements PlanDeAccionServiceInterface
{
    protected PlanDeAccionRepositoryInterface $repository;

    public function __construct(PlanDeAccionRepositoryInterface $repository)
    {
        // El constructor debe inyectar la Interfaz del Repository
        $this->repository = $repository;
    }
    
    // Aquí implementas los métodos definidos en la interfaz:
    public function listar(): \Illuminate\Support\Collection
    {
        return $this->repository->obtenerTodos();
    }
    
    // ... Implementa el resto de los métodos (crear, eliminar, obtener, cambiarActivo, buscar)
    
    public function cambiarActivo(int $id): bool
    {
        // Lógica de negocio si es necesario
        return $this->repository->cambiarActivo($id);
    }

    public function crear(array $data): PlanDeAccion
    {
        return $this->repository->crear($data);
    }
    
    public function eliminar(int $id): bool
    {
        return $this->repository->eliminar($id);
    }
    
    public function obtener(int $id): ?PlanDeAccion
    {
        return $this->repository->obtenerPorId($id); 
    }
    
    public function buscar(string $q): \Illuminate\Support\Collection
    {
        return $this->repository->buscarPorCriterio($q);
    }
   
    public function obtenerPlanesParaPrincipal(Request $request): array
    {
        $planes = $this->repository->obtenerPlanesFiltrados($request);
        $aulas = $this->obtenerAulasParaFiltro();
        
        return [
            'planesDeAccion' => $planes, 
            'aulas' => $aulas, 
        ];
    }

    public function obtenerAulasParaFiltro(): Collection
    {
        return $this->repository->obtenerAulasParaFiltro()
            ->map(function ($aula) {
                return (object)[
                    'id' => $aula->id_aula,
                    'descripcion' => $aula->descripcion, // accessor
                ];
            });
    }

}