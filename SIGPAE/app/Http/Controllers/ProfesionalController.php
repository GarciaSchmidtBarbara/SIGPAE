<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\ProfesionalServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;
use App\Models\Profesional;


class ProfesionalController extends Controller {
    protected $profesionalService;

    public function __construct(ProfesionalServiceInterface $profesionalService) {
        $this->profesionalService = $profesionalService;
    }

    public function vista(Request $request) {
        $query = Profesional::with('persona');

        if ($request->filled('nombre')) {
            $nombre = strtolower($this->quitarTildes($request->nombre));
            $query->whereHas('persona', function ($q) use ($nombre) {
                $q->whereRaw("LOWER(unaccent(nombre::text)) LIKE ?", ["%{$nombre}%"]);
            });
        }

       if ($request->filled('apellido')) {
            $apellido = strtolower($this->quitarTildes($request->apellido));
            $query->whereHas('persona', function ($q) use ($apellido) {
                $q->whereRaw("LOWER(unaccent(apellido)) LIKE ?", ["%{$apellido}%"]);
            });
        }

        if ($request->filled('documento')) {
            $query->whereHas('persona', fn($q) => $q->where('dni', 'like', '%' . $request->documento . '%'));
        }

        if ($request->filled('profesion')) {
            $profesion_siglas = $request->profesion;
            $query->where('siglas', $profesion_siglas);
        }

        $usuarios = $query->get();
        
        return view('usuarios.principal', compact('usuarios'));
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

    public function store(Request $request): JsonResponse {
        try {
            // Pasamos todo el payload al servicio; el service separará persona/profesional
            $payload = $request->all();

            $profesional = $this->profesionalService->createProfesional($payload);
            return response()->json($profesional, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function update(Request $request, int $id): JsonResponse {
        try {
            // Permitimos que el payload contenga tanto datos de persona como datos del profesional
            $data = $request->all();

            $profesional = $this->profesionalService->updateProfesional($id, $data);
            return response()->json($profesional);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
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
            ->withErros($validator)
            ->withInputs();
        }

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

}