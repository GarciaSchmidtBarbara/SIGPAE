<?php

namespace App\Services\Interfaces;

use Illuminate\Support\Collection as ISupportCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use App\Models\Profesional;

interface ProfesionalServiceInterface
{
    public function getAllProfesionales(): Collection;
    public function getProfesionalById(int $id): ?Profesional;
    public function crearProfesional(array $data): Profesional;
    public function updateProfesional(int $id, array $data): Profesional;
    public function deleteProfesional(int $id): bool;
    public function getProfesionalByMatricula(string $matricula): ?Profesional;
    public function getProfesionalWithPersona(int $id): ?Profesional;
    public function getAllProfesionalesWithPersona(): Collection;
    public function obtenerTodasLasSiglas(): ISupportCollection;
    public function filtrar(Request $request): LengthAwarePaginator;
    public function cambiarActivo(int $id): bool;
    public function registrarConActivacion(array $data): Profesional;
    public function findByEmail(string $email): ?Profesional;
    public function activarCuenta(string $email, string $token, array $data): Profesional;
    public function desactivarCuenta(int $idProfesional): bool;
    public function actualizarContrasenia(int $idProfesional, string $newPassword): bool;
    public function resetContrasenia(string $email, string $token, string $newPassword): bool;
    public function actualizarPerfil(int $idProfesional, array $data): Profesional;
}