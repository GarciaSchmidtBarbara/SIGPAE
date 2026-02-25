<?php

namespace App\Repositories\Interfaces;

use App\Models\Evento;
use App\Enums\TipoEvento;
use Illuminate\Support\Collection;
use Carbon\Carbon;

interface EventoRepositoryInterface
{
    //Obtiene todos los eventos
    public function all(array $relations = []): Collection;

    //Encuentra un evento por su ID
    public function find(int $eventoId, array $relations = []): ?Evento;

    //Crea un nuevo evento
    public function create(array $data): Evento;

    //Actualiza un evento existente.
    public function update(int $eventoId, array $data): bool;

    //Elimina un evento por su ID.
    public function delete(int $eventoId): bool;

    //Obtiene todos los eventos creados por un profesional específico.
    public function getByCreador(int $profesionalId): Collection;

    //Obtiene los eventos donde un profesional es asistente
    public function getAsistidosPorProfesional(int $profesionalId, bool $confirmado = null): Collection;

    //Adjunta o sincroniza profesionales asistentes a un evento.
    public function syncProfesionales(Evento $evento, array $profesionalIds): void;

    //Adjunta o sincroniza alumnos a un evento
    public function syncAlumnos(Evento $evento, array $alumnoIds): void;

    //Obtiene eventos en un rango de fechas para el calendario
    public function getEventosByDateRange(string $start, string $end): Collection;

    //Obtiene evento con todas sus relaciones cargadas
    public function findWithRelations(int $eventoId): ?Evento;

    //Obtiene eventos futuros
    public function getFuturos(): Collection;

    //Obtiene eventos pasados
    public function getPasados(): Collection;

    //Obtiene eventos por tipo
    public function getByTipo(TipoEvento $tipo): Collection;

    //Obtiene eventos entre dos fechas
    public function getBetweenDates(Carbon $inicio, Carbon $fin): Collection;

    //Obtiene eventos que tienen alumnos asociados
    public function getWithAlumnos(): Collection;

    //Obtiene eventos de un alumno específico
    public function getByAlumno(int $alumnoId): Collection;

    //Obtiene eventos que tienen recordatorio configurado
    public function getWithRecordatorio(): Collection;

    //Cuenta eventos por tipo
    public function countByTipo(TipoEvento $tipo): int;

    //Verifica si un evento existe
    public function exists(int $eventoId): bool;
}