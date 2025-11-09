<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\ProfesionalServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
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
}