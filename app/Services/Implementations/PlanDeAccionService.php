<?php

namespace App\Services\Implementations;

use App\Services\Interfaces\PlanDeAccionServiceInterface;
use App\Services\Interfaces\AlumnoServiceInterface;
use App\Services\Interfaces\ProfesionalServiceInterface;
use App\Repositories\Interfaces\PlanDeAccionRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Models\PlanDeAccion;
use App\Enums\TipoPlan;
use App\Enums\EstadoPlan;
use App\Models\Alumno;
use App\Models\Aula;
use App\Models\Profesional;


class PlanDeAccionService implements PlanDeAccionServiceInterface
{
    protected PlanDeAccionRepositoryInterface $repository;
    protected AlumnoServiceInterface $serviceAlumno;
    protected ProfesionalServiceInterface $serviceProfesional;

    public function __construct(PlanDeAccionRepositoryInterface $repository, AlumnoServiceInterface $serviceAlumno, ProfesionalServiceInterface $serviceProfesional)
    {
        $this->repository = $repository;
        $this->serviceAlumno = $serviceAlumno;
        $this->serviceProfesional = $serviceProfesional;
    }

    public function crear(array $data): PlanDeAccion
    {
        return $this->repository->crear($data);
    }

    public function actualizar(int $id, array $data): ?PlanDeAccion
    {
        return $this->repository->actualizar($id, $data);
    }

    public function filtrar(Request $request): Collection
    {
        return $this->repository->filtrar($request);
    }

    public function obtenerTodos(): Collection
    {
        return $this->repository->obtenerTodos();
    }
    public function obtenerTodosConRelaciones(): Collection
    {
        return $this->repository->obtenerTodosConRelaciones();
    }
    
    public function obtenerAulas(): Collection
    {
        return $this->repository->obtenerAulas();
    }

    public function obtenerTipos(): Collection
    {
        return collect(TipoPlan::cases())->map(fn($tipo) => $tipo->value);
    }

    public function cambiarActivo(int $id): bool
    {
        return $this->repository->cambiarActivo($id);
    }


    public function crearEvaluacion(int $idPlan, array $data): bool
    {
        $plan = $this->repository->buscarPorId($idPlan);

        if (!$plan || $plan->estado_plan !== EstadoPlan::ABIERTO) {
            return false;
        }

        return $this->repository->crearEvaluacion($idPlan, $data);
    }

    public function buscarPorId(int $id): ?PlanDeAccion
    {
        return $this->repository->buscarPorId($id); 
    }
    
    public function eliminar(int $id): bool
    {
        return $this->repository->eliminar($id);
    }

    public function obtenerEliminados(): Collection
{
    return $this->repository->obtenerEliminados();
}

    public function restaurar(int $id): bool
    {
        return $this->repository->restaurar($id);
    }

    public function eliminarDefinitivo(int $id): bool
    {
        return $this->repository->eliminarDefinitivo($id);
    }
    
    public function datosParaFormulario(?int $id = null): array
    {
        $plan = $id ? $this->repository->buscarPorIdConRelaciones($id) : null;

        $alumnos = $this->serviceAlumno->listar();

        $alumnosJson = $alumnos->mapWithKeys(function ($al) {
            $persona = $al->persona;
            return [
                $al->id_alumno => [
                    'id' => $al->id_alumno,
                    'nombre' => $persona->nombre,
                    'apellido' => $persona->apellido,
                    'dni' => $persona->dni,
                    'fecha_nacimiento' => optional($persona->fecha_nacimiento)->format('d/m/Y'),
                    'nacionalidad' => $persona->nacionalidad ?? 'N/A',
                    'domicilio' => $persona->domicilio ?? 'N/A',
                    'edad' => optional($persona->fecha_nacimiento)->age,
                    'curso' => $al->aula ? ($al->aula->curso . '° ' . $al->aula->division) : 'N/A',
                    'curso' => $al->aula?->descripcion ?? 'N/A',
                    'aula_id' => $al->fk_id_aula,
                ]
            ];
        });

        // Alumno individual
        $initialAlumnoId = '';
        $initialAlumnoInfo = null;
        if ($plan && $plan->tipo_plan->value === 'INDIVIDUAL') {
            $alumno = $plan->alumnos->first();
            if ($alumno) {
                $initialAlumnoId = $alumno->id_alumno;
                $initialAlumnoInfo = $alumnosJson[$initialAlumnoId] ?? null;
            }
        }
        // alumnos seleccionados (modo grupal/individual)
        $alumnosSeleccionados = $plan?->alumnos->map(function ($al) {
            $persona = $al->persona;
            return [
                'id'       => $al->id_alumno,
                'nombre'   => $persona->nombre,
                'apellido' => $persona->apellido,
                'dni'      => $persona->dni,
                'curso'    => $al->aula?->descripcion,
                'aula_id'  => $al->fk_id_aula,
            ];
        }) ?? collect();

        //profesionales (objetos completos para Alpine)
        $profesionalesSeleccionados = $plan?->profesionalesParticipantes->map(function ($prof) {
            $persona = $prof->persona;
            return [
                'id'        => $prof->id_profesional,
                'nombre'    => $persona->nombre ?? null,
                'apellido'  => $persona->apellido ?? null,
                'profesion' => $prof->profesion ?? 'N/A',
            ];
        })->values()->toArray() ?? [];
        $profesionales = $this->serviceProfesional->getAllProfesionalesWithPersona();

        //aulas
        $aulasSeleccionadas = $plan?->aulas->pluck('id_aula')->toArray() ?? [];
        $aulas = $this->repository->obtenerModelosAulas();

        //intervenciones relacionadas
        $intervencionesAsociadas = collect();
        if ($plan) {
            $intervencionesAsociadas = $plan->intervenciones()
                ->get()
                ->map(function ($i) {
                    return [
                        'id_intervencion' => $i->id_intervencion,
                        'fecha_hora_intervencion' => $i->fecha_hora_intervencion->format('d/m/Y H:i'),
                        'tipo_intervencion' => $i->tipo_intervencion,
                        'estado' => $i->activo ? 'Activo' : 'Inactivo',
                    ];
                });
        }
        return [
            'plan' => $plan,
            'alumnos' => $alumnos,
            'aulas' => $aulas,
            'aulasSeleccionadas' => $aulasSeleccionadas,
            'profesionales' => $profesionales,
            'alumnosJson' => $alumnosJson,
            'initialAlumnoId' => $initialAlumnoId,
            'initialAlumnoInfo' => $initialAlumnoInfo,
            'alumnosSeleccionados' => $alumnosSeleccionados,
            'profesionalesSeleccionados' => $profesionalesSeleccionados,
            'intervencionesAsociadas' => $intervencionesAsociadas,
            
        ];
    }

    public function obtenerParaEvaluacion(int $id): PlanDeAccion
    {
        $plan = $this->repository->buscarPorId($id);

        if (!$plan) {
            throw new \Exception('El plan no existe.');
        }

        if ($plan->estado_plan !== EstadoPlan::ABIERTO) {
            throw new \Exception('El plan debe estar abierto para evaluarse.');
        }

        return $plan;
    }

    //DESPUES DE CREAR LA EVALUACION, CAMBIAR EL ESTADO DEL PLAN A CERRADO
    public function guardarEvaluacion(int $id, array $data): void
    {
        $plan = $this->repository->buscarPorId($id);

        if (!$plan) {
            throw new \Exception('El plan no existe.');
        }

        if ($plan->estado_plan !== EstadoPlan::ABIERTO) {
            throw new \Exception('El plan ya está cerrado.');
        }

        if ($this->repository->yaTieneEvaluacion($id)) {
            throw new \Exception('Este plan ya fue evaluado.');
        }

        $this->repository->crearEvaluacion([
            'fk_id_plan_de_accion' => $plan->id_plan_de_accion,
            'tipo' => 'final',
            'criterios' => $data['criterios'],
            'observaciones' => $data['observaciones'] ?? null,
            'conclusiones' => $data['conclusiones'],
        ]);
        $this->repository->actualizarEstado(
            $plan->id_plan_de_accion,
            EstadoPlan::CERRADO
        );
    }

}