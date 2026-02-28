<?php

namespace App\Services\Implementations;

use App\Repositories\Interfaces\IntervencionRepositoryInterface;
use App\Services\Interfaces\IntervencionServiceInterface;
//otros sevrice
use App\Services\Interfaces\PlanDeAccionServiceInterface;
use App\Services\Interfaces\AlumnoServiceInterface;
use App\Services\Interfaces\ProfesionalServiceInterface;

use App\Models\Intervencion;
use App\Enums\TipoIntervencion;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;

class IntervencionService implements IntervencionServiceInterface
{
    protected IntervencionRepositoryInterface $repository;
    protected AlumnoServiceInterface $serviceAlumno;
    protected ProfesionalServiceInterface $serviceProfesional;
    protected PlanDeAccionServiceInterface $servicePlan;

    public function __construct(IntervencionRepositoryInterface $repository, AlumnoServiceInterface $serviceAlumno,
    ProfesionalServiceInterface $serviceProfesional, PlanDeAccionServiceInterface $servicePlan) {
        $this->repository = $repository;
        $this->serviceAlumno = $serviceAlumno;
        $this->serviceProfesional = $serviceProfesional;
        $this->servicePlan = $servicePlan;
    }

    public function crear(array $data): Intervencion
    {
        return $this->repository->crear($data);
    }

    public function actualizar(int $id, array $data): Intervencion
    {
        return $this->repository->actualizar($id, $data);
    }

    public function eliminar(int $id): bool
    {
        return $this->repository->eliminar($id);
    }

    public function cambiarActivo(int $id): bool
    {
        return $this->repository->cambiarActivo($id);
    }

    public function obtenerTipos(): Collection
    {
        return collect(TipoIntervencion::cases())->map(fn($tipo) => $tipo->value);
    }

    public function obtenerAulas(): Collection
    {
        return $this->repository->obtenerAulas();
    }

    public function filtrar(Request $request): Collection
    {
        return $this->repository->filtrar($request);
    }
    
    public function buscarPorId(int $id): ?Intervencion
    {
        return $this->repository->buscarPorId($id);
    }

    public function formatearParaVista(Collection $intervenciones): Collection
    {
       return $intervenciones->map(function ($intervencion) {
            $alumnos = $intervencion->alumnos->map(fn($al) => $al->persona ? "{$al->persona->nombre} {$al->persona->apellido}" : 'N/A')->implode(', ');
            $profesionales = $intervencion->profesionales->map(fn($p) => $p->persona ? "{$p->persona->nombre} {$p->persona->apellido}" : 'N/A');
            $otros = $intervencion->otros_asistentes_i->map(fn($as) => "{$as->nombre} {$as->apellido}");
            $generador = $intervencion->profesionalGenerador?->persona;
            $generadorNombre = $generador ? "{$generador->nombre} {$generador->apellido}" : null;

            $todosProfesionales = collect()
                ->merge($profesionales)
                ->merge($otros)
                ->when($generadorNombre, fn($c) => $c->push($generadorNombre))
                ->unique()
                ->implode(', ');

            return [
                'id_intervencion' => $intervencion->id_intervencion,
                'fecha_hora_intervencion' => optional($intervencion->fecha_hora_intervencion)->format('d/m/Y H:i'),
                'tipo_intervencion' => $intervencion->tipo_intervencion,
                'alumnos' => $alumnos ?: 'Sin alumnos',
                'profesionales' => $todosProfesionales ?: 'Sin participantes',
                'activo' => $intervencion->activo,
            ];
        });
    }

    public function guardarOtrosAsistentes(Intervencion $intervencion, array $filas): Intervencion
    {
        return $this->repository->guardarOtrosAsistentes($intervencion, $filas);
    }

    public function datosParaFormulario(?int $id = null): array
    {
        $intervencion = $id ? $this->repository->buscarPorIdConRelaciones($id) : null;

        // Alumnos
        $alumnos = $this->serviceAlumno->listar();
        $alumnosJson = $alumnos->mapWithKeys(function ($al) {
            $persona = $al->persona;
            return [
                $al->id_alumno => [
                    'id' => $al->id_alumno,
                    'nombre' => $persona->nombre,
                    'apellido' => $persona->apellido,
                    'dni' => $persona->dni,
                    'curso' => $al->aula?->descripcion,
                    'aula_id' => $al->fk_id_aula,
                ]
            ];
        });

        // Alumnos seleccionados
        $alumnosSeleccionados = $intervencion?->alumnos->map(function ($al) {
            $persona = $al->persona;
            return [
                'id' => $al->id_alumno,
                'nombre' => $persona->nombre,
                'apellido' => $persona->apellido,
                'dni' => $persona->dni,
                'curso' => $al->aula?->descripcion,
                'aula_id' => $al->fk_id_aula,
            ];
        }) ?? collect();

        // Profesionales
        $profesionales = $this->serviceProfesional->getAllProfesionalesWithPersona();
        $profesionalesSeleccionados = $intervencion?->profesionales->map(function ($prof) {
            $persona = $prof->persona;
            return [
                'id' => $prof->id_profesional,
                'nombre' => $persona->nombre ?? null,
                'apellido' => $persona->apellido ?? null,
                'profesion' => $prof->profesion ?? 'N/A',
            ];
        }) ?? collect();

        // Aulas
        $aulas = $this->repository->obtenerAulas();
        $aulasSeleccionadas = $intervencion?->aulas->pluck('id_aula')->toArray() ?? [];

        // Planes de acción
        $planes = $this->servicePlan->obtenerTodos();

        // Otros asistentes
        $otrosAsistentes = $intervencion?->otros_asistentes_i->map(fn($as) => [
            'nombre' => $as->nombre,
            'apellido' => $as->apellido,
            'descripcion' => $as->descripcion,
        ]) ?? collect();

        return [
            'intervencion' => $intervencion,
            'alumnos' => $alumnos,
            'profesionales' => $profesionales,
            'aulas' => $aulas,
            'planes' => $planes,
            'alumnosJson' => $alumnosJson,
            'alumnosSeleccionados' => $alumnosSeleccionados,
            'profesionalesSeleccionados' => $profesionalesSeleccionados,
            'aulasSeleccionadas' => $aulasSeleccionadas,
            'otrosAsistentes' => $otrosAsistentes->values()->toArray(),
        ];
    }

}
