<?php

namespace App\Repositories\Eloquent;

use App\Models\Evento;
use App\Models\EsInvitadoA;
use App\Enums\TipoEvento;
use App\Repositories\Interfaces\EventoRepositoryInterface;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class EventoRepository implements EventoRepositoryInterface
{
    public function find(int $eventoId, array $relations = []): ?Evento
    {
        $query = Evento::query();
        
        if (!empty($relations)) {
            $query->with($relations);
        }
        
        return $query->find($eventoId);
    }

    public function all(array $relations = [], array $filters = []): Collection
    {
        $query = Evento::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        if (!empty($filters['tipo_evento'])) {
            $query->where('tipo_evento', $filters['tipo_evento']);
        }

        return $query->latest('fecha_hora')->get();
    }

    public function create(array $data): Evento
    {
        return Evento::create($data);
    }

    public function update(int $eventoId, array $data): bool
    {
        $evento = Evento::find($eventoId);
        if (!$evento) {
            return false;
        }

        return $evento->update($data);
    }

    public function delete(int $eventoId): bool
    {
        return (bool) Evento::whereKey($eventoId)->delete();
    }

    public function getByCreador(int $profesionalId): Collection
    {
        return Evento::where('fk_id_profesional_creador', $profesionalId)
            ->with(['profesionalCreador.persona'])
            ->latest('fecha_hora')
            ->get();
    }

    public function getAsistidosPorProfesional(int $profesionalId, bool $confirmado = null): Collection
    {
        $query = Evento::whereHas('esInvitadoA', function ($q) use ($profesionalId, $confirmado) {
            $q->where('fk_id_profesional', $profesionalId);
            if ($confirmado !== null) {
                $q->where('confirmado', $confirmado);
            }
        })->with(['profesionalCreador.persona', 'esInvitadoA']);

        return $query->latest('fecha_hora')->get();
    }

    public function syncProfesionales(Evento $evento, array $profesionalIds): void
    {
        // La lógica se maneja en el Service usando EsInvitadoA directamente
    }

    public function syncAlumnos(Evento $evento, array $alumnoIds): void
    {
        $evento->alumnos()->sync($alumnoIds);
    }

    public function getEventosByDateRange(string $start, string $end): Collection
    {
        return Evento::whereBetween('fecha_hora', [$start, $end])
            ->with(['profesionalCreador.persona', 'esInvitadoA'])
            ->orderBy('fecha_hora', 'asc')
            ->get();
    }

    public function getEventosParaProfesional(int $profesionalId, string $start, string $end): Collection
    {
        return Evento::whereBetween('fecha_hora', [$start, $end])
            ->where(function ($q) use ($profesionalId) {
                $q->where('fk_id_profesional_creador', $profesionalId)
                  ->orWhereHas('esInvitadoA', fn($q2) => $q2->where('fk_id_profesional', $profesionalId));
            })
            ->with([
                'profesionalCreador.persona',
                'esInvitadoA' => fn($q) => $q->where('fk_id_profesional', $profesionalId),
            ])
            ->orderBy('fecha_hora', 'asc')
            ->get();
    }

    public function findWithRelations(int $eventoId): ?Evento
    {
        return Evento::with([
            'profesionalCreador.persona',
            'esInvitadoA.profesional.persona',
            'alumnos.persona',
            'alumnos.aula',
            'aulas'
        ])->find($eventoId);
    }

    public function getFuturos(): Collection
    {
        return Evento::where('fecha_hora', '>', now())
            ->orderBy('fecha_hora', 'asc')
            ->get();
    }

    public function getPasados(): Collection
    {
        return Evento::where('fecha_hora', '<', now())
            ->orderBy('fecha_hora', 'desc')
            ->get();
    }

    public function getByTipo(TipoEvento $tipo): Collection
    {
        return Evento::where('tipo_evento', $tipo)
            ->latest('fecha_hora')
            ->get();
    }

    public function getBetweenDates(Carbon $inicio, Carbon $fin): Collection
    {
        return Evento::whereBetween('fecha_hora', [$inicio, $fin])
            ->orderBy('fecha_hora', 'asc')
            ->get();
    }

    public function getWithAlumnos(): Collection
    {
        return Evento::has('alumnos')
            ->with('alumnos')
            ->latest('fecha_hora')
            ->get();
    }

    public function getByAlumno(int $alumnoId): Collection
    {
        return Evento::whereHas('alumnos', function ($query) use ($alumnoId) {
            $query->where('id_alumno', $alumnoId);
        })
        ->with('alumnos')
        ->latest('fecha_hora')
        ->get();
    }

    public function getWithRecordatorio(): Collection
    {
        return Evento::whereNotNull('periodo_recordatorio')
            ->latest('fecha_hora')
            ->get();
    }

    public function getDerivacionesPendientesRecordatorio(): Collection
    {
        return Evento::where('tipo_evento', TipoEvento::DERIVACION_EXTERNA->value)
            ->where('periodo_recordatorio', '>', 0)
            ->whereNotNull('fecha_hora')
            ->get();
    }

    public function countByTipo(TipoEvento $tipo): int
    {
        return Evento::where('tipo_evento', $tipo)->count();
    }

    public function exists(int $eventoId): bool
    {
        return Evento::where('id_evento', $eventoId)->exists();
    }

    public function vincularInvitados(int $eventoId, array $invitados): void
    {
        foreach ($invitados as $prof) {
            if (!empty($prof['id'])) {
                EsInvitadoA::create([
                    'fk_id_evento' => $eventoId,
                    'fk_id_profesional' => $prof['id'],
                    'confirmacion' => $prof['confirmado'] ?? false,
                    'asistio' => $prof['asistio'] ?? false,
                ]);
            }
        }
    }

    public function reemplazarInvitados(int $eventoId, array $invitados): void
    {
        EsInvitadoA::where('fk_id_evento', $eventoId)->delete();
        $this->vincularInvitados($eventoId, $invitados);
    }

    public function sincronizarAulasEvento(int $eventoId, array $aulaIds): void
    {
        $evento = Evento::find($eventoId);
        if ($evento) {
            $evento->aulas()->sync($aulaIds);
        }
    }

    public function vincularAlumnosEvento(int $eventoId, array $alumnoIds): void
    {
        $evento = Evento::find($eventoId);
        if ($evento) {
            $evento->alumnos()->attach($alumnoIds);
        }
    }

    public function sincronizarAlumnosEvento(int $eventoId, array $alumnoIds): void
    {
        $evento = Evento::find($eventoId);
        if ($evento) {
            $evento->alumnos()->sync($alumnoIds);
        }
    }

    public function obtenerEventosDelDiaPorProfesional(int $profesionalId, string $fecha): Collection
    {
        return Evento::with('profesionalCreador.persona')
            ->where(function ($q) use ($profesionalId) {
                $q->where('fk_id_profesional_creador', $profesionalId)
                  ->orWhereHas('esInvitadoA', fn($q2) => $q2->where('fk_id_profesional', $profesionalId));
            })
            ->whereDate('fecha_hora', $fecha)
            ->orderBy('fecha_hora')
            ->get();
    }

    public function obtenerProximosEventosPorProfesional(int $profesionalId, string $desde, string $hasta, int $limite): Collection
    {
        return Evento::with('profesionalCreador.persona')
            ->where(function ($q) use ($profesionalId) {
                $q->where('fk_id_profesional_creador', $profesionalId)
                  ->orWhereHas('esInvitadoA', fn($q2) => $q2->where('fk_id_profesional', $profesionalId));
            })
            ->whereBetween('fecha_hora', [$desde, $hasta])
            ->orderBy('fecha_hora')
            ->limit($limite)
            ->get();
    }

    public function actualizarConfirmacionInvitado(int $eventoId, int $profesionalId, bool $confirmado): bool
    {
        $invitacion = EsInvitadoA::where('fk_id_evento', $eventoId)
            ->where('fk_id_profesional', $profesionalId)
            ->first();

        if (!$invitacion) {
            return false;
        }

        $invitacion->confirmacion = $confirmado;
        return $invitacion->save();
    }

    public function obtenerDatosRelacionesEvento(int $eventoId): array
    {
        $evento = $this->find($eventoId);

        if (!$evento) {
            return [];
        }

        $profesionalesEvento = $evento->esInvitadoA()
            ->with('profesional.persona')
            ->get()
            ->map(function ($inv) {
                return [
                    'id' => $inv->profesional->id_profesional,
                    'invitado' => true,
                    'confirmado' => $inv->confirmacion ?? false,
                    'asistio' => $inv->asistio ?? false,
                ];
            })->toArray();

        $alumnosEvento = $evento->alumnos()
            ->with('persona', 'aula')
            ->get()
            ->toArray();

        $cursosEvento = $evento->aulas()
            ->pluck('id_aula')
            ->toArray();

        return [
            'evento' => $evento,
            'profesionalesEvento' => $profesionalesEvento,
            'alumnosEvento' => $alumnosEvento,
            'cursosEvento' => $cursosEvento,
        ];
    }
}
