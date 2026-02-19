<?php

namespace App\Services\Interfaces;

use App\Models\Evento;
use Illuminate\Support\Collection;

interface EventoServiceInterface
{
    public function listarTodos(): Collection;
    
    public function obtenerPorId(int $id): ?Evento;
    
    public function crear(array $data): Evento;
    
    public function actualizar(int $id, array $data): bool;
    
    public function eliminar(int $id): bool;
    
    public function crearConParticipantes(array $data): Evento;
    
    public function actualizarConParticipantes(int $id, array $data): bool;
    
    public function crearDerivacionExterna(array $data): Evento;

    public function actualizarDerivacionExterna(int $id, array $data): bool;
    
    public function obtenerEventosParaCalendario(string $start, string $end): array;
}
