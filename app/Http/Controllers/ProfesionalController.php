<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\ProfesionalServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Persona;
use App\Models\Profesional;
use App\Enums\Siglas;


class ProfesionalController extends Controller {
    protected $profesionalService;

    public function __construct(ProfesionalServiceInterface $profesionalService) {
        $this->profesionalService = $profesionalService;
    }

    public function vista(Request $request) {
        $usuarios = $this->profesionalService->filtrar($request);
        $siglas = $this->profesionalService->obtenerTodasLasSiglas();
        
        return view('usuarios.principal', compact('usuarios', 'siglas'));
    }

    public function index(): JsonResponse {
        $profesionales = $this->profesionalService->getAllProfesionalesWithPersona();
        return response()->json($profesionales);
    }

    public function show(int $id): JsonResponse {
        $profesional = $this->profesionalService->getProfesionalWithPersona($id);
        if (!$profesional) {
            return response()->json(['message' => 'Profesional no encontrado'], 404);
        }
        return response()->json($profesional);
    }

    public function store(Request $request): RedirectResponse {
        $request->validate([
            'nombre' => 'required|string|max:225',
            'apellido' => 'required|string|max:225',
            'dni' => 'required|string|max:20|unique:personas,dni',
            'email' => 'required|email|unique:profesionales,email',
        ]);

        DB::transaction(function () use ($request) {

            $persona = Persona::create([
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'dni' => $request->dni,
            ]);

            $usuarioGenerado = strtolower($request->nombre . '.' . $request->apellido);

            if (Profesional::where('usuario', $usuarioGenerado)->exists()) {
                $usuarioGenerado .= rand(1, 99);
            }

            $profesional = Profesional::create([
                'fk_id_persona' => $persona->id_persona,
                'email' => $request->email,
                'usuario' => $usuarioGenerado,
                'contrasenia' => Str::random(12), //temporal
                'activo' => false,
            ]);

            Password::broker('profesionales')->sendResetLink([
                'email' => $profesional->email,
            ]);
        });

        return back()->with('success', 'usuario creado y correo de activación enviado');
    }

    public function crearEditar() {
        $siglas = collect(Siglas::cases())->map(fn($sigla) => $sigla->value);
        $usuarioData = Session::get('usuario_temp', []);
        
        return view('usuarios.crear-editar', compact('usuarioData', 'siglas'));
    }

    public function update(Request $request, int $id): RedirectResponse {
        try {
            // Permitimos que el payload contenga tanto datos de persona como datos del profesional
            $data = $request->all();
            $profesional = $this->profesionalService->updateProfesional($id, $data);
            return redirect()
                ->route('usuarios.principal')
                ->with('success', 'Usuario creado correctamente');

        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(int $id): JsonResponse {
        if ($this->profesionalService->deleteProfesional($id)) {
            return response()->json(null, 204);
        }
        return response()->json(['message' => 'Profesional no encontrado'], 404);
    }

    public function perfil()
    {
        // Obtener el profesional logueado con su relación 'persona'
        $prof = auth()->user();
        return view('perfil.principal', compact('prof'));
    }

    public function actualizarPerfil(Request $request)
    {
        $prof = auth()->user()->load('persona');

        $validator = Validator::make($request->all(), [
            'nombre'    => 'required|string|max:255',
            'apellido'  => 'required|string|max:255',
            'profesion' => 'required|string|max:255',
            'siglas'    => 'required|string|max:10',
            'usuario'   => 'required|string|max:50|unique:profesionales,usuario,' . $prof->id_profesional . ',id_profesional',
            'email'     => 'required|email|max:255|unique:profesionales,email,' . $prof->id_profesional . ',id_profesional',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
            ->with('errors', 'error al actualizar datos.')
            ->withErrors($validator)
            ->withInput();
        }

        $validated = $validator->validated();

        try {
            // Actualizar persona
            $prof->persona->update([
                'nombre'   => $validated['nombre'],
                'apellido' => $validated['apellido'],
            ]);
            // Actualizar profesional
            $prof->update([
                'profesion' => $validated['profesion'],
                'siglas'    => $validated['siglas'],
                'usuario'   => $validated['usuario'],
                'email'     => $validated['email'],
            ]);
            // Si querés que responda con JSON (por ejemplo, si usás fetch/Axios)
            // return response()->json(['message' => 'Perfil actualizado correctamente.']);

            // O si lo usás con un form tradicional:
            return redirect()->back()->with('success', 'Perfil actualizado correctamente.');
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()->with('error', 'Error al actualizar el perfil.');
        }
    }

    public function cambiarActivo(int $id): RedirectResponse
    {
        $resultado = $this->profesionalService->cambiarActivo($id);
        $mensaje = $resultado
            ? ['success' => 'El estado del usuario fue actualizado correctamente.']
            : ['error' => 'No pudo realizarse la actualización de estado del usuario.'];

        return redirect()->route('usuarios.principal')->with($mensaje);
    }

    /*
    public function editar(int $id)
    {
        $usuario = $this->profesionalService->obtener($id);
        if (!$usuario) {
            return redirect()->route('usuarios.principal')->with('error', 'Usuario no encontrado.');
        }

        $siglas = $this->profesionalService->obtenerTodasLasSiglas();

        $usuarioData = [
            'dni' => $usuario->persona->dni,
            'nombre' => $usuario->persona->nombre,
            'apellido' => $usuario->persona->apellido,
            'fecha_nacimiento' => $usuario->persona->fecha_nacimiento,
            'nacionalidad' => $usuario->persona->nacionalidad,
            'profesion' => $usuario->profesion,
            'siglas' => $usuario->siglas,
        ];

        //Guardar el ID en sesión para saber que estamos editando
        Session::put('editando_usuario_id', $id);

        return view('usuarios.crear-editar', compact('usuarioData', 'siglas', 'usuario'))->with('modo', 'editar');
    }*/


}