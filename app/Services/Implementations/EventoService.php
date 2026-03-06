<?php

namespace App\Services\Implementations;

use App\Models\Evento;
use App\Repositories\Interfaces\EventoRepositoryInterface;
use App\Services\Interfaces\EventoServiceInterface;
use App\Services\Interfaces\AulaServiceInterface;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class EventoService implements EventoServiceInterface
{
    protected EventoRepositoryInterface $repo;
    protected AulaServiceInterface $aulaService;

    public function __construct(EventoRepositoryInterface $repo, AulaServiceInterface $aulaService)
    {
        $this->repo = $repo;
        $this->aulaService = $aulaService;
    }

    public function listarTodos(array $filters = []): Collection
    {
        return $this->repo->all(['esInvitadoA'], $filters);
    }

    public function obtenerPorId(int $id): ?Evento
    {
        return $this->repo->find($id);
    }

    public function crear(array $data): Evento
    {
        return $this->repo->create($data);
    }

    public function actualizar(int $id, array $data): bool
    {
        return $this->repo->update($id, $data);
    }

    public function eliminar(int $id): bool
    {
        return $this->repo->delete($id);
    }

    public function crearConParticipantes(array $data): Evento
    {
        \DB::beginTransaction();
        try {
            $profesionalId = auth()->check() ? auth()->user()->getAuthIdentifier() : null;
            
            if (!$profesionalId) {
                throw new \Exception('Usuario no autenticado');
            }
            
            $eventoData = [
                'tipo_evento' => $data['tipo_evento'],
                'fecha_hora' => $data['fecha_hora'],
                'lugar' => $data['lugar'] ?? null,
                'notas' => $data['notas'] ?? null,
                'periodo_recordatorio' => $data['periodo_recordatorio'] ?? null,
                'fk_id_profesional_creador' => $profesionalId,
            ];

            $evento = $this->repo->create($eventoData);

            if (!empty($data['profesionales'])) {
                $this->repo->vincularInvitados($evento->id_evento, $data['profesionales']);
            }

            if (!empty($data['cursos'])) {
                $this->repo->sincronizarAulasEvento($evento->id_evento, $data['cursos']);
            }

            if (!empty($data['alumnos'])) {
                $alumnoIds = array_map(function($a) {
                    return is_array($a) ? $a['id'] : $a;
                }, $data['alumnos']);
                $this->repo->vincularAlumnosEvento($evento->id_evento, $alumnoIds);
            }

            \DB::commit();
            return $evento->load(['profesionalCreador.persona', 'esInvitadoA.profesional.persona', 'alumnos.persona', 'aulas']);
        } catch (\Throwable $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    public function actualizarConParticipantes(int $id, array $data): bool
    {
        \DB::beginTransaction();
        try {
            $eventoData = [
                'tipo_evento' => $data['tipo_evento'],
                'fecha_hora' => $data['fecha_hora'],
                'lugar' => $data['lugar'] ?? null,
                'notas' => $data['notas'] ?? null,
                'periodo_recordatorio' => $data['periodo_recordatorio'] ?? null,
            ];

            $actualizado = $this->repo->update($id, $eventoData);
            
            if (!$actualizado) {
                throw new \Exception('Evento no encontrado');
            }

            $this->repo->reemplazarInvitados($id, $data['profesionales'] ?? []);

            $this->repo->sincronizarAulasEvento($id, $data['cursos'] ?? []);

            if (!empty($data['alumnos'])) {
                $alumnoIds = array_map(function($a) {
                    return is_array($a) ? $a['id'] : $a;
                }, $data['alumnos']);
                $this->repo->sincronizarAlumnosEvento($id, $alumnoIds);
            } else {
                $this->repo->sincronizarAlumnosEvento($id, []);
            }

            \DB::commit();
            return true;
        } catch (\Throwable $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    public function crearDerivacionExterna(array $data): Evento
    {
        \DB::beginTransaction();
        try {
            $profesionalId = auth()->check() ? auth()->user()->getAuthIdentifier() : null;
            
            if (!$profesionalId) {
                throw new \Exception('Usuario no autenticado');
            }
            
            $eventoData = [
                'tipo_evento' => 'DERIVACION_EXTERNA',
                'fecha_hora' => $data['fecha'] ?? now(),
                'lugar' => $data['lugar'] ?? null,
                'notas' => $data['notas'] ?? null,
                'profesional_tratante' => $data['profesional_tratante'] ?? null,
                'periodo_recordatorio' => $data['periodo_recordatorio'] ?? null,
                'fk_id_profesional_creador' => $profesionalId,
            ];

            $evento = $this->repo->create($eventoData);

            if (!empty($data['alumnos'])) {
                $this->repo->vincularAlumnosEvento($evento->id_evento, $data['alumnos']);
            }

            \DB::commit();
            return $evento;
        } catch (\Throwable $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    public function actualizarDerivacionExterna(int $id, array $data): bool
    {
        \DB::beginTransaction();
        try {
            $eventoData = [
                'fecha_hora' => $data['fecha'] ?? now(),
                'lugar' => $data['lugar'] ?? null,
                'notas' => $data['notas'] ?? null,
                'profesional_tratante' => $data['profesional_tratante'] ?? null,
                'periodo_recordatorio' => $data['periodo_recordatorio'] ?? null,
            ];

            $actualizado = $this->repo->update($id, $eventoData);

            if (!$actualizado) {
                throw new \Exception('Derivación no encontrada');
            }

            $this->repo->sincronizarAlumnosEvento($id, $data['alumnos'] ?? []);

            \DB::commit();
            return true;
        } catch (\Throwable $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    public function dejarDeRecordar(int $id): bool
    {
        return $this->repo->update($id, [
            'periodo_recordatorio'   => 0,
            'ultimo_recordatorio_at' => null,
        ]);
    }

    public function obtenerDerivacionesPendientesRecordatorio(): Collection
    {
        return $this->repo->getDerivacionesPendientesRecordatorio();
    }

    public function obtenerEventosParaCalendario(string $start, string $end): array
    {
        $profesional = auth()->user();
        if (!$profesional) return [];

        $eventos = $this->repo->getEventosParaProfesional($profesional->id_profesional, $start, $end);

        return $eventos->map(function ($evento) {
            return [
                'id' => $evento->id_evento,
                'title' => $this->formatearTituloEvento($evento),
                'start' => $evento->fecha_hora->format('Y-m-d'),
                'allDay' => true,
                'extendedProps' => [
                    'tipo' => $evento->tipo_evento?->value ?? 'general',
                    'lugar' => $evento->lugar,
                    'notas' => $evento->notas,
                    'creador' => $evento->profesionalCreador?->persona?->nombre ?? 'Sin asignar',
                    'hora' => $evento->fecha_hora->format('H:i'),
                ],
            ];
        })->values()->toArray();
    }

    private function formatearTituloEvento(Evento $evento): string
    {
        $tipos = [
            'BANDA' => 'Banda',
            'RG' => 'Reunión Gabinete',
            'RD' => 'Reunión Directivos',
            'CITA_FAMILIAR' => 'Cita Familiar',
            'DERIVACION_EXTERNA' => 'Derivación Externa'
        ];
        
        $tipoValue = $evento->tipo_evento?->value ?? 'general';
        $tipo = $tipos[$tipoValue] ?? 'Evento';
        
        return $tipo;
    }

    public function obtenerEventosDelDia(int $profesionalId): Collection
    {
        $hoy = Carbon::today()->toDateString();
        return $this->repo->obtenerEventosDelDiaPorProfesional($profesionalId, $hoy);
    }

    public function obtenerProximosEventos(int $profesionalId, int $meses = 2, int $limite = 3): Collection
    {
        $hoy = Carbon::today()->toDateString();
        $fin = Carbon::now()->addMonths($meses)->endOfDay()->toDateTimeString();

        return $this->repo->obtenerProximosEventosPorProfesional($profesionalId, $hoy, $fin, $limite);
    }

    public function actualizarConfirmacion(int $eventoId, int $profesionalId, bool $confirmado): bool
    {
        return $this->repo->actualizarConfirmacionInvitado($eventoId, $profesionalId, $confirmado);
    }

    public function obtenerDatosVistaEvento(int $eventoId): array
    {
        $cursos = $this->aulaService->obtenerTodas();

        if ($eventoId <= 0) {
            return [
                'evento' => null,
                'cursos' => $cursos,
                'profesionalesEvento' => [],
                'alumnosEvento' => [],
                'cursosEvento' => [],
            ];
        }

        $datos = $this->repo->obtenerDatosRelacionesEvento($eventoId);

        if (empty($datos)) {
            return [];
        }

        $datos['cursos'] = $cursos;
        return $datos;
    }
}
