<?php

namespace App\Services\Implementations;

use App\Services\Interfaces\PlanDeAccionServiceInterface;
use App\Repositories\Interfaces\PlanDeAccionRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Models\PlanDeAccion;
use App\Enums\TipoPlan;
use App\Models\Alumno;
use App\Models\Aula;
use App\Models\Profesional;


class PlanDeAccionService implements PlanDeAccionServiceInterface
{
    protected PlanDeAccionRepositoryInterface $repository;

    public function __construct(PlanDeAccionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function listar(): Collection
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
    
    public function actualizar(int $id, array $data): ?PlanDeAccion
    {
        return $this->repository->actualizar($id, $data);
    }

    public function eliminar(int $id): bool
    {
        return $this->repository->eliminar($id);
    }
    
    public function obtener(int $id): ?PlanDeAccion
    {
        return $this->repository->obtenerPorId($id); 
    }
    
    public function buscar(string $q): Collection
    {
        return $this->repository->buscarPorCriterio($q);
    }
   
    public function filtrar(Request $request): Collection
    {
        return $this->repository->obtenerPlanesFiltrados($request);
    }

    public function obtenerAulasParaFiltro(): Collection
    {
        return $this->repository->obtenerAulasParaFiltro();
    }

    public function obtenerTipos(): Collection
    {
        return collect(TipoPlan::cases())->map(fn($tipo) => $tipo->value);
    }

    public function datosParaFormulario(?int $id = null): array
    {
        $plan = $id ? $this->repository->buscarPorIdConRelaciones($id) : null;

        $alumnos = \App\Models\Alumno::with(['persona', 'aula'])->get();

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
                ]
            ];
        });

        // === Alumno individual (solo si aplica) ===
        $initialAlumnoId = '';
        $initialAlumnoInfo = null;
        if ($plan && $plan->tipo_plan->value === 'INDIVIDUAL') {
            $alumno = $plan->alumnos->first();
            if ($alumno) {
                $initialAlumnoId = $alumno->id_alumno;
                $initialAlumnoInfo = $alumnosJson[$initialAlumnoId] ?? null;
            }
        }
        // === Alumnos seleccionados (modo grupal) ===
        $alumnosSeleccionados = $plan?->alumnos->map(function ($al) {
            $persona = $al->persona;
            return [
                'id_alumno' => $al->id_alumno,
                'nombre' => $persona->nombre,
                'apellido' => $persona->apellido,
                'dni' => $persona->dni,
                'curso' => $al->aula ? ($al->aula->curso . '° ' . $al->aula->division) : 'N/A',
            ];
        }) ?? collect();
        // === Profesionales seleccionados ===
        $profesionalesSeleccionados = $plan?->profesionalesParticipantes->pluck('id_profesional')->toArray() ?? [];

        // === Aulas seleccionadas ===
        $aulasSeleccionadas = $plan?->aulas->pluck('id_aula')->toArray() ?? [];

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
            'aulas' => Aula::all(),
            'profesionales' => Profesional::with('persona')->get(),
            'alumnosJson' => $alumnosJson,
            'initialAlumnoId' => $initialAlumnoId,
            'initialAlumnoInfo' => $initialAlumnoInfo,
            'alumnosSeleccionados' => $alumnosSeleccionados,
            'profesionalesSeleccionados' => $profesionalesSeleccionados,
            'aulasSeleccionadas' => $aulasSeleccionadas,
            'intervencionesAsociadas' => $intervencionesAsociadas,
        ];
    }



}