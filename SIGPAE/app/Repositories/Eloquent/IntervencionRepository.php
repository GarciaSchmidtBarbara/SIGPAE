<?php

namespace App\Repositories\Eloquent;

use App\Models\Intervencion;
use App\Repositories\Interfaces\IntervencionRepositoryInterface;
use Illuminate\Support\Collection;
use App\Models\Aula;

class IntervencionRepository implements IntervencionRepositoryInterface
{
    public function obtenerTodos()
    {
        return Intervencion::query()
            ->with([
                'planDeAccion',
                'profesionalGenerador',
                'profesionales',
                'aulas',
                'alumnos',
                'evaluacionIntervencionEspontanea',
                'documentos',
                'otros_asistentes_i',
                'planillas',
            ])
            ->where('activo', true)
            ->orderBy('fecha_hora_intervencion', 'desc')
            ->get();
    }

    public function buscarPorId(int $id): ?Intervencion
    {
        return Intervencion::query()
            ->with([
                'planDeAccion',
                'profesionalGenerador',
                'profesionales',
                'aulas',
                'alumnos',
                'evaluacionIntervencionEspontanea',
                'documentos',
                'otros_asistentes_i',
                'planillas',
            ])
            ->find($id);
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

        if (!$intervencion) {
            return false;
        }

        return $intervencion->update(['activo' => false]);
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
}
