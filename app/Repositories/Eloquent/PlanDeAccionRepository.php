<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Interfaces\PlanDeAccionRepositoryInterface;
use App\Models\PlanDeAccion;
use App\Models\EvaluacionDePlan;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Aula;
use App\Enums\EstadoPlan;


class PlanDeAccionRepository implements PlanDeAccionRepositoryInterface
{
   protected PlanDeAccion $model;

    public function __construct(PlanDeAccion $model)
    {
        $this->model = $model;
    }

    public function obtenerTodos(): Collection
    {
        return $this->model->newQuery()->get();
    }
    public function obtenerTodosConRelaciones(): Collection
    {
        return $this->model->newQuery()
            ->with($this->withRelations())
            ->get();
    }

    
    public function crear(array $data): PlanDeAccion
    {
        $tipo = strtoupper($data['tipo_plan'] ?? '');

        // 1. Crear el plan base
        $plan = $this->model->create([
            'tipo_plan' => $tipo,
            'objetivos' => $data['objetivos'] ?? null,
            'acciones' => $data['acciones'] ?? null,
            'observaciones' => $data['observaciones'] ?? null,
            'estado_plan' => EstadoPlan::ABIERTO->value,
            'activo' => true,
            'fk_id_profesional_generador' => $data['fk_id_profesional_generador'],
        ]);

        // 2. asociaciones segun tipo de plan
        if ($tipo === 'INDIVIDUAL') {

            // 1 solo alumno y sin aula
            if (!empty($data['alumnos']) && is_array($data['alumnos'])) {
                $plan->alumnos()->sync([(int) $data['alumnos'][0]]);
            }
            $plan->aulas()->sync([]);

        } elseif ($tipo === 'GRUPAL') {
            // minimo 2 alumnos y aula opcional
            if (!empty($data['alumnos']) && is_array($data['alumnos'])) {
                $plan->alumnos()->sync(array_map('intval', $data['alumnos']));
            } else {
                $plan->alumnos()->sync([]);
            }
            if (!empty($data['aula'])) {
                $plan->aulas()->sync([(int) $data['aula']]);
            } else {
                $plan->aulas()->sync([]);
            }

        } elseif ($tipo === 'INSTITUCIONAL') {
            //Sin alumnos ni aulas
            $plan->alumnos()->sync([]);
            $plan->aulas()->sync([]);
        }

        // 3. Profesionales participantes
        if (!empty($data['profesionales']) && is_array($data['profesionales'])) {
            $plan->profesionalesParticipantes()->sync(array_map('intval', $data['profesionales']));
        }

        return $plan;
    }

    public function actualizar(int $id, array $data): ?PlanDeAccion
    {
        $plan = $this->model->find($id);
        if (!$plan) return null;

        $tipo = strtoupper($data['tipo_plan'] ?? $plan->tipo_plan);

        // 1. Actualizar campos base
        $plan->update([
            'tipo_plan' => $tipo,
            'objetivos' => $data['objetivos'] ?? $plan->objetivos,
            'acciones' => $data['acciones'] ?? $plan->acciones,
            'observaciones' => $data['observaciones'] ?? $plan->observaciones,
        ]);

        // 2. Actualizar relaciones de alumnos (misma convención 'alumnos')
        if ($tipo === 'INDIVIDUAL') {
            if (!empty($data['alumnos']) && is_array($data['alumnos'])) {
                $plan->alumnos()->sync([ (int) $data['alumnos'][0] ]);
            } elseif (!empty($data['alumno_seleccionado'])) {
                $plan->alumnos()->sync([ (int) $data['alumno_seleccionado'] ]);
            } else {
                $plan->alumnos()->detach();
            }
        } elseif ($tipo === 'GRUPAL') {
            if (!empty($data['alumnos']) && is_array($data['alumnos'])) {
                $plan->alumnos()->sync(array_map('intval', $data['alumnos']));
            } elseif (!empty($data['alumnos_grupal']) && is_array($data['alumnos_grupal'])) {
                $plan->alumnos()->sync(array_map('intval', $data['alumnos_grupal']));
            } else {
                $plan->alumnos()->detach();
            }
        } else {
            // Si es INSTITUCIONAL u otro tipo, limpiar alumnos para evitar residuos
            $plan->alumnos()->detach();
        }

        if (isset($data['aula'])) {
            if ($data['aula']) {
                $plan->aulas()->sync([ (int) $data['aula'] ]);
            } else {
                $plan->aulas()->detach();
            }
        }

        if (isset($data['profesionales']) && is_array($data['profesionales'])) {
            $plan->profesionalesParticipantes()->sync(array_map('intval', $data['profesionales']));
        } else {
            // si viene vacío, desasociar todos
            if (array_key_exists('profesionales', $data) && empty($data['profesionales'])) {
                $plan->profesionalesParticipantes()->detach();
            }
        }

        // recargar relaciones si es necesario
        return $plan->fresh();
    }

    public function eliminar(int $id): bool
    {
        return $this->model->destroy($id);
    }

    public function obtenerEliminados(): Collection
    {
        return $this->model->onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->get();
    }

    public function restaurar(int $id): bool
    {
        $plan = $this->model->onlyTrashed()->find($id);
        return $plan ? $plan->restore() : false;
    }

    public function eliminarDefinitivo(int $id): bool
    {
        $plan = $this->model->onlyTrashed()->find($id);
        return $plan ? $plan->forceDelete() : false;
    }
    
    public function actualizarEstado($id, EstadoPlan $estado)
    {
        return PlanDeAccion::where('id_plan_de_accion', $id)
            ->update(['estado_plan' => $estado->value]);
    }


    public function cambiarActivo(int $id): bool
    {
        $plan = PlanDeAccion::find($id);

        if (!$plan) {
            return false;
        }

        $plan->estado_plan = $plan->estado_plan->value === EstadoPlan::ABIERTO->value 
            ? EstadoPlan::CERRADO->value 
            : EstadoPlan::ABIERTO->value;

        return $plan->save();
    }


    public function buscarPorIdPersona(int $idPersona): ?PlanDeAccion
    {
        // Lógica para buscar un plan asociado a una persona
        return $this->model->whereHas('alumnos', fn($q) => $q->where('fk_id_alumno', $idPersona))->first();
    }

    public function filtrar(Request $request): Collection
    {
        $query = $this->model->newQuery();

        // Carga Eager de todas las relaciones necesarias para la vista
        $query->with([
            'alumnos.persona',
            'alumnos.aula',
            'aulas', 
            'profesionalGenerador.persona', 
            'profesionalesParticipantes.persona',
        ]);

        // 1. Filtrar por tipo de plan
        if ($tipo = $request->get('tipo')) {
            $query->where('tipo_plan', $tipo);
        }

        // 2. Filtrar por Estado
        if ($estado = $request->get('estado')) {
            $estadoDb = match ($estado) { 
                'activos' => EstadoPlan::ABIERTO->value, 
                'inactivos' => EstadoPlan::CERRADO->value, 
                default => null, 
            }; 
            if ($estadoDb) { 
                $query->where('estado_plan', $estadoDb); 
            } 
        }

        // 3. Filtrar por Curso
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
        
        // Ordenamiento por 'activo' (primero activos) y luego por fecha.
        $query->orderByDesc('activo')
            ->orderByDesc('created_at');

        return $query->get();
    }

    public function obtenerAulas(): Collection
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

    public function obtenerModelosAulas():Collection
    {
        return Aula::all();
    }

    public function buscarPorId(int $id): ?PlanDeAccion
    {
        return PlanDeAccion::find($id);
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

    public function crearEvaluacion(array $data)
    {
        return EvaluacionDePlan::create($data);
    }


    public function yaTieneEvaluacion($id)
    {
        return EvaluacionDePlan::where('fk_id_plan_de_accion', $id)->exists();
    }
}