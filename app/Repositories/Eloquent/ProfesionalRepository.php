<?php

namespace App\Repositories\Eloquent;

use App\Models\Profesional;
use App\Repositories\Interfaces\ProfesionalRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ProfesionalRepository implements ProfesionalRepositoryInterface
{
    protected Profesional $model;

    public function __construct(Profesional $profesional)
    {
        $this->model = $profesional;
    }

    public function cambiarActivo(int $id): bool
    {
        $usuario = Profesional::with('persona')->find($id);
        if ($usuario && $usuario->persona) {
            $usuario->persona->activo = !$usuario->persona->activo;
            return $usuario->persona->save();
        }
        return false;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function find(int $id): ?Profesional
    {
        return $this->model->find($id);
    }

    public function crear(array $data): Profesional
    {
        return Profesional::crearProfesional($data);
    }

    public function update(int $id, array $data): Profesional
    {
        $profesional = $this->model->findOrFail($id);
        $profesional->update($data);
        return $profesional->fresh();
    }

    public function delete(int $id): bool
    {
        $profesional = $this->model->find($id);
        if (!$profesional) {
            return false;
        }
        return (bool) $profesional->delete();
    }

    public function findByPersona(int $personaId): ?Profesional
    {
        return $this->model->where('fk_id_persona', $personaId)->first();
    }

    public function findWithPersona(int $id): ?Profesional
    {
        return $this->model->with('persona')->find($id);
    }

    public function allWithPersona(): Collection
    {
        return $this->model->with('persona')->get();
    }

    public function findByMatricula(string $matricula): ?Profesional
    {
        return $this->model->where('matricula', $matricula)->first();
    }

    public function findByEmail(string $email): ?Profesional
    {
        return $this->model->where('email', $email)->first();
    }

    public function desactivar(int $id): bool
    {
        $usuario = Profesional::with('persona')->find($id);
        if ($usuario && $usuario->persona) {
            $usuario->persona->activo = false;
            return $usuario->persona->save();
        }
        return false;
    }

    public function filtrar(\Illuminate\Http\Request $request): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Profesional::with('persona');

        if ($request->filled('buscar')) {
            $buscar = strtolower($request->buscar);

            $query->where(function ($q) use ($buscar) {
                $q->whereHas('persona', function ($sub) use ($buscar) {
                    $sub->whereRaw("LOWER(nombre) LIKE ?", ["%{$buscar}%"])
                        ->orWhereRaw("LOWER(apellido) LIKE ?", ["%{$buscar}%"])
                        ->orWhere("dni", 'like', "%{$buscar}%");
                })
                ->orWhereRaw("LOWER(siglas) LIKE ?", ["%{$buscar}%"])
                ->orWhereRaw("LOWER(profesion) LIKE ?", ["%{$buscar}%"]);
            });
        }

        return $query
            ->orderBy('id_profesional', 'desc')
            ->paginate(10)
            ->withQueryString();
    }

    public function existeUsuario(string $usuario): bool
    {
        return Profesional::where('usuario', $usuario)->exists();
    }

    public function buscarTokenReset(string $email): ?object
    {
        return \DB::table('password_resets')
            ->where('email', $email)
            ->first();
    }

    public function eliminarTokenReset(string $email): bool
    {
        return \DB::table('password_resets')
            ->where('email', $email)
            ->delete() > 0;
    }
}
