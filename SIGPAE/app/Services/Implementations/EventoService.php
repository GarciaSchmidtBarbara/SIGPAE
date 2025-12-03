<?php

namespace App\Services\Implementations;

use App\Models\Evento;
use App\Models\EsInvitadoA;
use App\Repositories\Interfaces\EventoRepositoryInterface;
use App\Services\Interfaces\EventoServiceInterface;
use Illuminate\Support\Collection;

class EventoService implements EventoServiceInterface
{
    protected EventoRepositoryInterface $repo;

    public function __construct(EventoRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function listarTodos(): Collection
    {
        return $this->repo->all();
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
            // Obtener el ID del profesional autenticado
            $profesionalId = auth()->check() ? auth()->user()->getAuthIdentifier() : null;
            
            if (!$profesionalId) {
                throw new \Exception('Usuario no autenticado');
            }
            
            // Crear evento básico
            $eventoData = [
                'tipo_evento' => $data['tipo_evento'],
                'fecha_hora' => $data['fecha_hora'],
                'lugar' => $data['lugar'] ?? null,
                'notas' => $data['notas'] ?? null,
                'fk_id_profesional_creador' => $profesionalId,
            ];

            $evento = $this->repo->create($eventoData);

            // Vincular profesionales invitados
            if (!empty($data['profesionales'])) {
                foreach ($data['profesionales'] as $prof) {
                    if (!empty($prof['id'])) {
                        EsInvitadoA::create([
                            'fk_id_evento' => $evento->id_evento,
                            'fk_id_profesional' => $prof['id'],
                            'confirmacion' => $prof['confirmado'] ?? false,
                            'asistio' => $prof['asistio'] ?? false,
                        ]);
                    }
                }
            }

            // Vincular cursos (aulas)
            if (!empty($data['cursos'])) {
                $evento->aulas()->attach($data['cursos']);
            }

            // Vincular alumnos
            if (!empty($data['alumnos'])) {
                $alumnoIds = array_map(function($a) {
                    return is_array($a) ? $a['id'] : $a;
                }, $data['alumnos']);
                $evento->alumnos()->attach($alumnoIds);
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
            ];

            $actualizado = $this->repo->update($id, $eventoData);
            
            if (!$actualizado) {
                throw new \Exception('Evento no encontrado');
            }

            // Cargar el evento para trabajar con las relaciones
            $evento = $this->repo->find($id);

            // Actualizar profesionales invitados
            EsInvitadoA::where('fk_id_evento', $id)->delete();
            if (!empty($data['profesionales'])) {
                foreach ($data['profesionales'] as $prof) {
                    if (!empty($prof['id'])) {
                        EsInvitadoA::create([
                            'fk_id_evento' => $id,
                            'fk_id_profesional' => $prof['id'],
                            'confirmacion' => $prof['confirmado'] ?? false,
                            'asistio' => $prof['asistio'] ?? false,
                        ]);
                    }
                }
            }

            // Actualizar cursos
            $evento->aulas()->sync($data['cursos'] ?? []);

            // Actualizar alumnos
            if (!empty($data['alumnos'])) {
                $alumnoIds = array_map(function($a) {
                    return is_array($a) ? $a['id'] : $a;
                }, $data['alumnos']);
                $evento->alumnos()->sync($alumnoIds);
            } else {
                $evento->alumnos()->sync([]);
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
            // Obtener el ID del profesional autenticado
            $profesionalId = auth()->check() ? auth()->user()->getAuthIdentifier() : null;
            
            if (!$profesionalId) {
                throw new \Exception('Usuario no autenticado');
            }
            
            // Construir las notas incluyendo el profesional tratante y la descripción externa
            $notasCompletas = $data['notas'] ?? '';
            
            if (!empty($data['descripcion_externa'])) {
                $notasCompletas = "DERIVACIÓN: " . $data['descripcion_externa'];
                if (!empty($data['notas'])) {
                    $notasCompletas .= "\n\nNOTAS: " . $data['notas'];
                }
            }
            
            if (!empty($data['profesional_tratante'])) {
                $notasCompletas .= "\n\nPROFESIONAL TRATANTE: " . $data['profesional_tratante'];
            }
            
            $eventoData = [
                'tipo_evento' => 'DERIVACION_EXTERNA',
                'fecha_hora' => $data['fecha'] ?? now(),
                'lugar' => $data['lugar'] ?? null,
                'notas' => $notasCompletas,
                'periodo_recordatorio' => $data['periodo_recordatorio'] ?? null,
                'fk_id_profesional_creador' => $profesionalId,
            ];

            $evento = $this->repo->create($eventoData);

            // Vincular alumnos si los hay
            if (!empty($data['alumnos'])) {
                $evento->alumnos()->attach($data['alumnos']);
            }

            \DB::commit();
            return $evento;
        } catch (\Throwable $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    public function obtenerEventosParaCalendario(string $start, string $end): array
    {
        $eventos = $this->repo->getEventosByDateRange($start, $end);
        
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
        })->toArray();
    }

    private function formatearTituloEvento(Evento $evento): string
    {
        // Mapeo de tipos a nombres legibles
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
}
