<?php

namespace App\Services\Implementations;
// Imports
// Profesional
use App\Repositories\Interfaces\ProfesionalRepositoryInterface;
use App\Repositories\Interfaces\PersonaRepositoryInterface;
use App\Services\Interfaces\ProfesionalServiceInterface;
use App\Models\Profesional;
// Persona
use App\Services\Interfaces\PersonaServiceInterface;
use App\Models\Persona;
// Illuminate
use Illuminate\Support\Collection as ISupportCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use InvalidArgumentException;
use App\Enums\Siglas;

class ProfesionalService implements ProfesionalServiceInterface
{
    protected ProfesionalRepositoryInterface $repo;
    protected PersonaRepositoryInterface $personaRepo;
    protected \App\Services\Interfaces\PersonaServiceInterface $personaService;

    public function __construct(ProfesionalRepositoryInterface $repo, PersonaRepositoryInterface $personaRepo, \App\Services\Interfaces\PersonaServiceInterface $personaService) {
        $this->repo = $repo;
        $this->personaRepo = $personaRepo;
        $this->personaService = $personaService;
    }

    public function getAllProfesionales(): Collection
    {
        return $this->repo->all();
    }

    public function getProfesionalById(int $id): ?Profesional
    {
        return $this->repo->find($id);
    }

    public function cambiarActivo(int $id): bool
    {
        return $this->repo->cambiarActivo($id);
    }

    // Lógica de búsqueda y filtrado
    public function filtrar(Request $request): LengthAwarePaginator
    {
        return $this->repo->filtrar($request);
    }

    public function crearProfesional(array $data): Profesional {
        \DB::beginTransaction();
        try {
            $formato = str_contains($data['fecha_nacimiento'], '/') ? 'd/m/Y' : 'Y-m-d';
            $fecha = \DateTime::createFromFormat($formato, $data['fecha_nacimiento']);
            $data['fecha_nacimiento'] = $fecha ? $fecha->format('Y-m-d') : null;

            $persona = $this->personaRepo->create([
                'dni' => $data['dni'],
                'nombre' => $data['nombre'],
                'apellido' => $data['apellido'],
                'fecha_nacimiento' => $data['fecha_nacimiento'],
                'activo' => true,
            ]);

            if (!$persona) {
                throw new \Exception('Error al crear la persona asociada');
            }

            $profesional = $this->repo->crear([
                'fk_id_persona' => $persona->id_persona,
                'usuario' => $data['usuario'],
                'profesion' => $data['profesion'],
                'siglas' => $data['siglas'],
                'email' => $data['email'],
                'contrasenia' => bcrypt($data['contrasenia']),
            ]);

            \DB::commit();
            return $profesional->load(['persona']);

        } catch (\Throwable $e) {
            \DB::rollBack();
            \Log::error('Error al crear usuario: '.$e->getMessage(), ['data' => $data]);
            throw new \Exception('Ocurrió un error al crear el usuario. '.$e->getMessage());
        }
    }

    public function updateProfesional(int $id, array $data): Profesional
    {
        $profesional = $this->getProfesionalById($id);
        if (!$profesional) {
            throw new InvalidArgumentException('Profesional no encontrado');
        }

        // Separar campos de persona y profesional
        $personaFields = array_intersect_key($data, array_flip([
            'nombre', 'apellido', 'dni', 'fecha_nacimiento', 'domicilio', 'nacionalidad'
        ]));

        $profesionalFields = array_intersect_key($data, array_flip([
            'matricula', 'especialidad', 'cargo', 'profesion', 'siglas', 'telefono', 'usuario', 'email', 'contrasenia'
        ]));

        try {
            DB::beginTransaction();

            // Si vienen campos de persona, actualizamos la Persona asociada
            if (!empty($personaFields)) {
                $fk = $profesional->fk_id_persona ?? null;
                if (!$fk) {
                    throw new InvalidArgumentException('Profesional no tiene persona asociada');
                }
                $this->personaService->updatePersona($fk, $personaFields);
            }

            // Actualizar el profesional con los campos propios
            if (!empty($profesionalFields)) {
                $profesional = $this->repo->update($id, $profesionalFields);
            }

            DB::commit();
            return $profesional;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteProfesional(int $id): bool
    {
        return $this->repo->delete($id);
    }

    public function getProfesionalByMatricula(string $matricula): ?Profesional
    {
        return $this->repo->findByMatricula($matricula);
    }

    public function getProfesionalWithPersona(int $id): ?Profesional
    {
        return $this->repo->findWithPersona($id);
    }

    public function getAllProfesionalesWithPersona(): Collection
    {
        return $this->repo->allWithPersona();
    }
    public function obtenerTodasLasSiglas(): ISupportCollection {
        // Devuelve una colección con todas las siglas posibles del enum
        return collect(Siglas::cases())->map(fn($sigla) => $sigla->value);
    }

    public function findByEmail(string $email): ?Profesional
    {
        return $this->repo->findByEmail($email);
    }

    public function registrarConActivacion(array $data): Profesional
    {
        return DB::transaction(function () use ($data) {
            $persona = $this->personaRepo->create([
                'nombre' => $data['nombre'],
                'apellido' => $data['apellido'],
                'dni' => $data['dni'],
            ]);

            $usuarioGenerado = strtolower($data['nombre'] . '.' . $data['apellido']);

            if ($this->repo->existeUsuario($usuarioGenerado)) {
                $usuarioGenerado .= rand(1, 99);
            }

            $profesional = $this->repo->crear([
                'fk_id_persona' => $persona->id_persona,
                'email' => $data['email'],
                'usuario' => $usuarioGenerado,
                'contrasenia' => \Str::random(12),
                'activo' => false,
            ]);

            \Password::broker('profesionales')->sendResetLink([
                'email' => $profesional->email,
            ]);

            return $profesional;
        });
    }

    public function activarCuenta(string $email, string $token, array $data): Profesional
    {
        $prof = $this->repo->findByEmail($email);

        if (!$prof) {
            throw new InvalidArgumentException('No se encontró el correo.');
        }

        $record = $this->repo->buscarTokenReset($email);

        if (!$record || !\Hash::check($token, $record->token)) {
            throw new InvalidArgumentException('Token inválido o expirado.');
        }

        DB::beginTransaction();
        try {
            // Actualizar persona
            $personaId = $prof->fk_id_persona;
            $this->personaService->updatePersona($personaId, [
                'fecha_nacimiento' => $data['fecha_nacimiento'],
            ]);

            // Actualizar profesional
            $this->repo->update($prof->id_profesional, [
                'contrasenia' => \Hash::make($data['password']),
                'telefono' => $data['telefono'],
                'profesion' => $data['profesion'],
                'siglas' => $data['siglas'],
            ]);

            $this->repo->eliminarTokenReset($email);

            DB::commit();
            return $prof->fresh();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function desactivarCuenta(int $idProfesional): bool
    {
        return $this->repo->desactivar($idProfesional);
    }

    public function actualizarContrasenia(int $idProfesional, string $newPassword): bool
    {
        $prof = $this->repo->find($idProfesional);
        if (!$prof) {
            return false;
        }
        $this->repo->update($idProfesional, [
            'contrasenia' => \Hash::make($newPassword),
        ]);
        return true;
    }

    public function resetContrasenia(string $email, string $token, string $newPassword): bool
    {
        $prof = $this->repo->findByEmail($email);

        if (!$prof) {
            throw new InvalidArgumentException('No se encontró el correo.');
        }

        $record = $this->repo->buscarTokenReset($email);

        if (!$record || !\Hash::check($token, $record->token)) {
            throw new InvalidArgumentException('Token inválido o expirado.');
        }

        $this->repo->update($prof->id_profesional, [
            'contrasenia' => \Hash::make($newPassword),
        ]);

        $this->repo->eliminarTokenReset($email);

        return true;
    }

    public function actualizarPerfil(int $idProfesional, array $data): Profesional
    {
        $personaFields = array_intersect_key($data, array_flip([
            'nombre', 'apellido',
        ]));

        $profesionalFields = array_intersect_key($data, array_flip([
            'profesion', 'siglas', 'usuario', 'email', 'telefono',
            'hora_envio_resumen_diario', 'notification_anticipation_minutos',
        ]));

        return $this->updateProfesional($idProfesional, array_merge($personaFields, $profesionalFields));
    }

    private function normalizarTexto(string $texto): string {
        return strtolower(strtr(iconv('UTF-8', 'ASCII//TRANSLIT', $texto), "´`^~¨", "     "));
    }
}
