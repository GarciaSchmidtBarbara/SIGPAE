<?php

namespace App\Repositories\Eloquent;

use App\Models\Documento;
use App\Repositories\Interfaces\DocumentoRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DocumentoRepository implements DocumentoRepositoryInterface
{
    protected Documento $model;

    public function __construct(Documento $model)
    {
        $this->model = $model;
    }

    private function withRelaciones(): array
    {
        return [
            'alumno.persona',
            'planDeAccion',
            'intervencion',
            'profesionalCarga.persona',
        ];
    }

    public function buscarPorId(int $id): ?Documento
    {
        return $this->model->newQuery()
            ->with($this->withRelaciones())
            ->find($id);
    }

    public function todos(): Collection
    {
        return $this->model->newQuery()
            ->with($this->withRelaciones())
            ->orderByDesc('fecha_hora_carga')
            ->get();
    }

    public function filtrar(Request $request): Collection
    {
        $query = $this->model->newQuery()
            ->with($this->withRelaciones());

        // Filtro por contexto
        if ($contexto = $request->get('contexto')) {
            $query->where('contexto', $contexto);
        }

        // Filtro por nombre (parcial, case-insensitive)
        if ($nombre = $request->get('nombre')) {
            $query->whereRaw('LOWER(nombre) LIKE ?', ['%' . strtolower($nombre) . '%']);
        }

        return $query->orderByDesc('fecha_hora_carga')->get();
    }

    public function crear(array $data): Documento
    {
        return $this->model->newQuery()->create($data);
    }

    public function eliminar(int $id): bool
    {
        $doc = $this->model->newQuery()->find($id);
        if (!$doc) {
            return false;
        }
        return (bool) $doc->delete();
    }
}
