<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Interfaces\PlanDeAccionRepositoryInterface;
use App\Models\PlanDeAccion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Contracts\Pagination\LengthAwarePaginator; // Importar para la paginación
use App\Models\Aula;

class PlanDeAccionRepository implements PlanDeAccionRepositoryInterface
{
   protected PlanDeAccion $model;

    public function __construct(PlanDeAccion $model)
    {
        $this->model = $model;
    }
    
    public function crear(array $data): PlanDeAccion
    {
        return $this->model->create($data);
    }

    public function eliminar(int $id): bool
    {
        return $this->model->destroy($id);
    }
    
    public function cambiarActivo(int $id): bool
    {
        $plan = PlanDeAccion::find($id);
        if ($plan) {
            $plan->activo = !$plan->activo;
            $plan->estado_plan = $plan->activo ? 'ABIERTO' : 'CERRADO';
            return $plan->save();
        }
        return false;
    }

    public function buscarPorIdPersona(int $idPersona): ?PlanDeAccion
    {
        // Lógica para buscar un plan asociado a una persona
        return $this->model->whereHas('alumnos', fn($q) => $q->where('fk_id_alumno', $idPersona))->first();
    }

    public function obtenerPlanesFiltrados(Request $request): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        //CLAVE: Carga Eager de todas las relaciones necesarias para la vista
        $query->with([
            'profesionalGenerador.persona', 
            'profesionalesParticipantes.persona', 
            'alumnos.persona', 
            'aulas' 
        ]);

        // 1. Filtrar por Tipo (tipo_plan)
        if ($tipo = $request->get('tipo')) {
            $query->where('tipo_plan', $tipo);
        }

        // 2. Filtrar por Estado (estado_plan)
        if ($estado = $request->get('estado')) {
            $query->where('estado_plan', $estado);
        }

        // 3. Filtrar por Curso/Aula
        if ($cursoId = $request->get('curso')) {
            $query->whereHas('aulas', fn($q) => $q->where('aulas.id_aula', $cursoId));
        }

        // 4. Filtrar por Alumno (búsqueda por nombre/DNI)
        if ($alumnoQuery = $request->get('alumno')) {
            $query->whereHas('alumnos.persona', function ($q) use ($alumnoQuery) {
                // Utiliza ILIKE para búsqueda insensible a mayúsculas/minúsculas (si usas PostgreSQL)
                $q->where('nombre', 'ILIKE', "%{$alumnoQuery}%") 
                  ->orWhere('apellido', 'ILIKE', "%{$alumnoQuery}%")
                  ->orWhere('dni', 'ILIKE', "%{$alumnoQuery}%");
            });
        }
        
        $query->orderBy('created_at', 'desc');
        return $query->paginate(15);
    }

    public function obtenerAulasParaFiltro(): Collection
    {
        return Aula::select('id_aula', 'curso', 'division')
           ->orderBy('curso')
           ->orderBy('division')
           ->get();
    }
    
    
    public function obtenerPorId(int $id): ?PlanDeAccion
    {
        return $this->model->find($id);
    }

    public function obtenerTodos(): Collection
    {
        return $this->model->all(); 
    }

    public function buscarPorIdConRelaciones(int $id)
    {
        return $this->model->with([
            'profesionalGenerador.persona',
            'profesionalesParticipantes.persona',
            'alumnos.persona',
            'aulas'
        ])->findOrFail($id);
    }

}