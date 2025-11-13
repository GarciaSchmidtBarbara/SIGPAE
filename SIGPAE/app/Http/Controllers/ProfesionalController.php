<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\ProfesionalServiceInterface;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    public function crearEditar() {
        $siglas = collect(Siglas::cases())->map(fn($sigla) => $sigla->value);
        $usuarioData = Session::get('usuario_temp', []);
        
        return view('usuarios.crear-editar', compact('usuarioData', 'siglas'));
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
}