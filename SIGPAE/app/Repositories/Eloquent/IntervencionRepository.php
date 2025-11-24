<?php

namespace App\Repositories\Eloquent;

use App\Models\Intervencion;
use App\Repositories\Interfaces\IntervencionRepositoryInterface;
use Illuminate\Support\Collection;
use App\Models\Aula;

class IntervencionRepository implements IntervencionRepositoryInterface
{
    private function withRelations()
    {
        return [
            'planDeAccion',
            'profesionalGenerador',
            'profesionales',
            'aulas',
            'alumnos',
            'evaluacionDeIntervencionEspontanea',
            'documentos',
            'otros_asistentes_i',
            'planillas',
        ];
    }
    public function obtenerTodos()
    {
        return Intervencion::query()
            ->with($this->withRelations())
            ->where('activo', true)
            ->orderBy('fecha_hora_intervencion', 'desc')
            ->get();
    }

    public function buscarPorId(int $id): ?Intervencion
    {
        return Intervencion::query()
            ->with($this->withRelations())
            ->find($id);
    }

    public function filtrar(array $filters = [])
    {
        $query = Intervencion::query()->with($this->withRelations());

        if (!empty($filters['tipo_intervencion'])) {
            $query->where('tipo_intervencion', $filters['tipo_intervencion']);
        }

        if (!empty($filters['nombre'])) {
            $query->whereHas('alumnos.persona', function ($q) use ($filters) {
                $q->where('nombre', 'like', "%{$filters['nombre']}%")
                ->orWhere('apellido', 'like', "%{$filters['nombre']}%")
                ->orWhere('dni', 'like', "%{$filters['nombre']}%");
            });
        }

        if (!empty($filters['aula_id'])) {
            $query->whereHas('aulas', fn($q) => $q->where('id_aula', $filters['aula_id']));
        }

        if (!empty($filters['fecha_desde'])) {
            $query->where('fecha_hora_intervencion', '>=', $filters['fecha_desde']);
        }

        if (!empty($filters['fecha_hasta'])) {
            $query->where('fecha_hora_intervencion', '<=', $filters['fecha_hasta']);
        }

        return $query->where('activo', true)
                    ->orderBy('fecha_hora_intervencion', 'desc')
                    ->get();
    }



    public function crear(array $data): Intervencion
    {
        return Intervencion::create($data);
    }

    public function actualizar(int $id, array $data): bool
    {
        $intervencion = $this->buscarPorId($id);

        if (!$intervencion) {
            return false;
        }

        return $intervencion->update($data);
    }

    public function eliminar(int $id): bool
    {
        $intervencion = $this->buscarPorId($id);
        if (!$intervencion) return false;

        return $intervencion->delete();
    }

    public function cambiarActivo(int $id): bool
    {
        $intervencion = $this->buscarPorId($id);
        if (!$intervencion) return false;
        $nuevoEstado = !$intervencion->activo;

        return $intervencion->update(['activo' => $nuevoEstado]);
    }

    public function obtenerAulasParaFiltro(): Collection
    {
        return Aula::select('id_aula', 'curso', 'division')
            ->orderBy('curso')
            ->orderBy('division')
            ->get()
            ->map(fn($aula) => (object)[
                'id' => $aula->id_aula,
                'descripcion' => $aula->curso . ' ° ' . $aula->division
            ]);
    }
}
