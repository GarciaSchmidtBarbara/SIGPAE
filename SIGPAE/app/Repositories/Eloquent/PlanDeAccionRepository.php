<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Interfaces\PlanDeAccionRepositoryInterface;
use App\Models\PlanDeAccion;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
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

    // El filtro completo se manejará mejor en el Service, como en tu módulo Alumno.
    public function filtrar(array $filtros): \Illuminate\Database\Eloquent\Builder
    {
        // En este ejemplo, solo devolvemos la query base para que el Service aplique el resto de la lógica.
        return PlanDeAccion::query();
    }

    public function buscarPorIdPersona(int $idPersona): ?PlanDeAccion
    {
        // Lógica para buscar un plan asociado a una persona
        return $this->model->whereHas('alumnos', fn($q) => $q->where('fk_id_alumno', $idPersona))->first();
    }

    public function obtenerPlanesFiltrados(Request $request): Collection
    {
        $query = $this->model->newQuery();

        // Carga Eager de todas las relaciones necesarias para la vista
        $query->with([
            'alumnos.persona',
            'aulas', 
            'profesionalGenerador.persona', 
            'profesionalesParticipantes.persona',
        ]);

        // 1. Filtrar por Tipo (tipo_plan)
        if ($tipo = $request->get('tipo')) {
            $query->where('tipo_plan', $tipo);
        }

        // 2. Filtrar por Estado (activo/inactivo, adaptado del módulo Alumnos)
        $estado = $request->get('estado', 'activos');
        if ($estado === 'activos') {
            $query->where('activo', true);
        } elseif ($estado === 'inactivos') {
            $query->where('activo', false);
        }

        // 3. Filtrar por Curso/Aula
        if ($cursoId = $request->get('curso')) {
            $query->whereHas('aulas', fn($q) => $q->where('aulas.id_aula', $cursoId));
        }

        // 4. Filtrar por Alumno (búsqueda por nombre/DNI)
        if ($alumnoQuery = $request->get('alumno')) {
            $query->whereHas('alumnos.persona', function ($q) use ($alumnoQuery) {
                 $q->where('nombre', 'ILIKE', "%{$alumnoQuery}%") 
                   ->orWhere('apellido', 'ILIKE', "%{$alumnoQuery}%")
                   ->orWhere('dni', 'ILIKE', "%{$alumnoQuery}%");
            });
        }
        
        $planes = $query->get();

        // Ordenamiento por 'activo' (primero activos/abiertos) y luego por fecha.
        return $planes->sort(function ($a, $b) {
            $activoA = $a->activo ? 1 : 0;
            $activoB = $b->activo ? 1 : 0;
            
            if ($activoA !== $activoB) {
                return $activoA > $activoB ? -1 : 1;
            }

            // Si el estado es el mismo, ordenar por la fecha más reciente (desc)
            return $b->created_at <=> $a->created_at; 
        })->values();
    }

    public function obtenerAulasParaFiltro(): Collection
    {
        $aulasData = Aula::select('id_aula', 'curso', 'division') 
            ->orderBy('curso') 
            ->orderBy('division')
            ->get();
            
        $mappedCollection = $aulasData->map(fn($aula) => (object)[
            'id' => $aula->id_aula, 
            'descripcion' => $aula->curso . ' ° ' . $aula->division 
        ]);

        return Collection::make($mappedCollection);
    }
    

    public function obtenerPorId(int $id): ?PlanDeAccion
    {
        return PlanDeAccion::find($id);
    }

    public function obtenerTodos(): Collection
    {
        return PlanDeAccion::all()
            ->sortByDesc('activo') 
            ->values();
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