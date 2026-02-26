<?php

namespace App\Repositories\Eloquent;

use App\Models\Familiar;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Interfaces\FamiliarRepositoryInterface;


class FamiliarRepository implements FamiliarRepositoryInterface
{
    protected Familiar $model;

    public function __construct(Familiar $familiar)
    {
        $this->model = $familiar;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function find(int $id): ?Familiar
    {
        return $this->model->find($id);
    }

    public function create(array $data): Familiar
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Familiar
    {
        $familiar = $this->model->findOrFail($id);
        $familiar->update($data);
        return $familiar->fresh();
    }

    public function delete(int $id): bool
    {
        $familiar = $this->model->find($id);
        if (!$familiar) {
            return false;
        }
        return (bool) $familiar->delete();
    }

    public function findByPersona(int $personaId): ?Familiar
    {
        return $this->model->where('fk_id_persona', $personaId)->first();
    }

    public function findWithPersona(int $id): ?Familiar
    {
        return $this->model->with('persona')->find($id);
    }

    public function allWithPersona(): Collection
    {
        return $this->model->with('persona')->get();
    }

    public function buscarPorTermino(string $termino): \Illuminate\Support\Collection
    {
        $like = '%' . str_replace('%', '', $termino) . '%';

        return Familiar::with('persona')
            ->whereHas('persona', function ($sub) use ($like) {
                $sub->where('dni', 'like', $like)
                    ->orWhere('nombre', 'ilike', $like)
                    ->orWhere('apellido', 'ilike', $like);
            })
            ->limit(10)
            ->get();
    }
}
