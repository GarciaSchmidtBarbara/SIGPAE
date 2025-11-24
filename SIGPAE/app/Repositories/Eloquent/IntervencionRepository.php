<?php

namespace App\Repositories\Eloquent;

use App\Models\Intervencion;
use App\Models\Aula;
use App\Repositories\Interfaces\IntervencionRepositoryInterface;
use Illuminate\Support\Collection;


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
            $nombre = $this->normalizarTexto($filters['nombre']);
            $query->whereHas('alumnos.persona', function ($q) use ($nombre) {
                $q->whereRaw("LOWER(unaccent(nombre::text)) LIKE ?", ["%{$nombre}%"])
                ->orWhereRaw("LOWER(unaccent(apellido::text)) LIKE ?", ["%{$nombre}%"])
                ->orWhereRaw("CAST(dni AS TEXT) LIKE ?", ["%{$nombre}%"]);
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

    private function normalizarTexto(string $texto): string
    {
        return strtolower(strtr(iconv('UTF-8', 'ASCII//TRANSLIT', $texto), "´`^~¨", "     "));
    }

    public function crear(array $data): Intervencion
    {
        $tipo = strtoupper($data['tipo_intervencion'] ?? '');

        // 1. Crear la intervencion base
        $intervencion = Intervencion::create([
            'fecha_hora_intervencion' => $data['fecha_hora_intervencion'],
            'lugar' => $data['lugar'] ?? null, 
            'modalidad' => $data['modalidad'] ?? null,
            'otra_modalidad' => $data['otra_modalidad'] ?? null,
            'temas_tratados' => $data['temas_tratados'] ?? null,
            'compromisos' => $data['compromisos'] ?? null,
            'observaciones' => $data['observaciones'] ?? null,
            'activo' => true,
            'tipo_intervencion' => $tipo,
            'fk_id_profesional_generador' => $data['fk_id_profesional_generador'],
        ]);

        // 2. ASOCIACIONES SEGÚN TIPO DE INTERVENCIÓN

        //Si es programada tiene un plan de accion asociado
        if ($tipo === 'PROGRAMADA' && !empty($data['plan_de_accion'])) {
            $intervencion->fk_id_plan_de_accion = (int) $data['plan_de_accion'];
            $intervencion->save();
        }

        //alumnos destinatarios
        if (!empty($data['alumnos']) && is_array($data['alumnos'])) {
            $intervencion->alumnos()->sync(array_map('intval', $data['alumnos']));
        }

        //aulas destinatarias
        if (!empty($data['aulas']) && is_array($data['aulas'])) {
            $intervencion->aulas()->sync(array_map('intval', $data['aulas']));
        }

        //profesionales participantes
        if (!empty($data['profesionales']) && is_array($data['profesionales'])) {
            $intervencion->profesionales()->sync(array_map('intval', $data['profesionales']));
        }

        //otros asistentes
        if (!empty($data['otros_asistentes_i'])) {
            $intervencion->otros_asistentes_i()->createMany(
                collect($data['otros_asistentes_i'])->map(fn($id) => ['fk_id_profesional' => (int) $id])->toArray()
            );
        }

        return $intervencion;
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
