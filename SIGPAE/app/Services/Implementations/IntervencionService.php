<?php

namespace App\Services\Implementations;

use App\Repositories\Interfaces\IntervencionRepositoryInterface;
use App\Services\Interfaces\IntervencionServiceInterface;
use App\Models\Intervencion;
use App\Enums\TipoIntervencion;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class IntervencionService implements IntervencionServiceInterface
{
    protected IntervencionRepositoryInterface $repository;

    public function __construct(IntervencionRepositoryInterface $repository) {
        $this->repository = $repository;
    }

    public function buscarPorId(int $id)
    {
        return $this->repository->buscarPorId($id);
    }

    public function crear(array $data)
    {
        DB::beginTransaction();
        try {
            $intervencion = $this->repository->crear($data);
            DB::commit();
            return $intervencion;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function editar(int $id, array $data): bool
    {
        DB::beginTransaction();
        try {
            $ok = $this->repository->editar($id, $data);

            if (!$ok) {
                throw new Exception('Intervención no encontrada.');
            }
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
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

    public function formatearParaVista(Collection $intervenciones): Collection
{
    return $intervenciones->map(function ($intervencion) {
        $alumnos = $intervencion->alumnos->map(function ($alumno) {
            $persona = $alumno->persona;
            return $persona ? "{$persona->nombre} {$persona->apellido}" : 'N/A';
        })->implode(', ');

        $profesionalesReune = $intervencion->profesionales->map(function ($profesional) {
            $persona = $profesional->persona;
            return $persona ? "{$persona->nombre} {$persona->apellido}" : 'N/A';
        });

        $otrosProfesionales = $intervencion->otros_asistentes_i->map(function ($asistente) {
            $persona = $asistente->profesional?->persona;
            return $persona ? "{$persona->nombre} {$persona->apellido}" : 'N/A';
        });

        $generador = $intervencion->profesionalGenerador?->persona;
        $generadorNombre = $generador ? "{$generador->nombre} {$generador->apellido}" : null;

        $todosProfesionales = collect()
            ->merge($profesionalesReune)
            ->merge($otrosProfesionales)
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

    public function guardarOtrosAsistentes(Intervencion $intervencion, array $filas)
    {
        return $this->repository->guardarOtrosAsistentes($intervencion, $filas);
    }



}
