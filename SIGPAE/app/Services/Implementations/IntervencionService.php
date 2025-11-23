<?php

namespace App\Services\Implementations;

use App\Repositories\Interfaces\IntervencionRepositoryInterface;
use App\Services\Interfaces\IntervencionServiceInterface;
use App\Models\Intervencion;
use App\Enums\TipoIntervencion;
use Illuminate\Support\Collection;

use Illuminate\Support\Facades\DB;
use Exception;

class IntervencionService implements IntervencionServiceInterface
{
    protected IntervencionRepositoryInterface $repo;
    public function __construct(IntervencionRepositoryInterface $repo) {
        $this->repo = $repo;
    }

    public function obtenerTodos()
    {
        return $this->repo->obtenerTodos();
    }

    public function buscar(int $id)
    {
        return $this->repo->buscarPorId($id);
    }

    public function crear(array $data)
    {
        DB::beginTransaction();
        try {
            $intervencion = $this->repo->crear($data);

            // aquí podrás sincronizar profesionales, aulas, alumnos...
            // ejemplo:
            // $intervencion->profesionales()->sync($data['profesionales'] ?? []);

            DB::commit();
            return $intervencion;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function actualizar(int $id, array $data): bool
    {
        DB::beginTransaction();
        try {
            $ok = $this->repo->actualizar($id, $data);

            if (!$ok) {
                throw new Exception('Intervención no encontrada.');
            }

            // sincronización ejemplo
            // $intervencion = $this->repo->buscarPorId($id);
            // $intervencion->profesionales()->sync($data['profesionales'] ?? []);

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function eliminar(int $id): bool
    {
        return $this->repo->eliminar($id);
    }

    public function cambiarActivo(int $id): bool
    {
        return $this->repo->cambiarActivo($id);
    }

    public function obtenerTipos(): Collection
    {
        return collect(TipoIntervencion::cases())->map(fn($tipo) => $tipo->value);
    }

    public function obtenerAulasParaFiltro(): Collection
    {
        return $this->repo->obtenerAulasParaFiltro();
    }
}
