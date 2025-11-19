<?php

namespace App\Services\Implementations;

use App\Repositories\PlanDeAccionRepository;
use App\Services\Interfaces\PlanDeAccionServiceInterface;
use App\Repositories\Interfaces\PlanDeAccionRepositoryInterface;
use Illuminate\Http\Request;
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
        // Tu repositorio no tiene un método 'obtener' directo, ajustamos la llamada:
        return $this->repository->obtenerPorId($id); // Asumiendo que crearás este método en el Repository
    }
    
    public function buscar(string $q): \Illuminate\Support\Collection
    {
        // Asumiendo que crearás un método de búsqueda en el Repository
        return $this->repository->buscarPorCriterio($q);
    }
   
    public function obtenerPlanesParaPrincipal(Request $request): array
    {
        // Llama al método optimizado con filtros y paginación
        $planes = $this->repository->obtenerPlanesFiltrados($request);
        
        // Asumimos que el Repository tiene un método para obtener la lista de aulas para el filtro
        $aulas = $this->repository->obtenerAulasParaFiltro(); // <-- Requiere un nuevo método en el Repository
        
        return [
            'planesDeAccion' => $planes, // El nombre de la variable que usaste en la vista
            'aulasContenido' => $aulas, // El nombre de la variable que usaste en la vista
        ];
    }
}