<?php

namespace App\Services\Implementations;

use App\Services\Interfaces\PlanDeAccionServiceInterface;
use App\Repositories\Interfaces\PlanDeAccionRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Models\PlanDeAccion;
use App\Enums\TipoPlan;


class PlanDeAccionService implements PlanDeAccionServiceInterface
{
    protected PlanDeAccionRepositoryInterface $repository;

    public function __construct(PlanDeAccionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function listar(): \Illuminate\Support\Collection
    {
        return $this->repository->obtenerTodos();
    }
    
    public function cambiarActivo(int $id): bool
    {
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
   
    public function filtrar(Request $request): Collection
    {
        // Delegamos la complejidad del filtrado y la carga de relaciones al Repository.
        return $this->repository->obtenerPlanesFiltrados($request);
    }

    public function obtenerAulasParaFiltro(): Collection
    {
        return $this->repository->obtenerAulasParaFiltro();
    }

    public function obtenerTipos(): Collection
    {
        // Retorna la lista de valores del Enum TipoPlan
        return collect(TipoPlan::cases())->map(fn($tipo) => $tipo->value);
    }

    public function datosParaFormulario(?int $id = null): array
    {
        $plan = $id ? $this->repository->buscarPorIdConRelaciones($id) : null;

        return [
            'plan' => $plan,
            'alumnos' => Alumno::with('persona')->get(),
            'aulas' => Aula::all(),
            'profesionales' => Profesional::with('persona')->get(),
        ];
    }

}