<?php

namespace App\Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\User\Services\Interfaces\ProfesionalServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProfesionalController extends Controller
{
    protected $profesionalService;

    public function __construct(ProfesionalServiceInterface $profesionalService)
    {
        $this->profesionalService = $profesionalService;
    }

    public function index(): JsonResponse
    {
        $profesionales = $this->profesionalService->getAllProfesionalesWithPersona();
        return response()->json($profesionales);
    }

    public function show(int $id): JsonResponse
    {
        $profesional = $this->profesionalService->getProfesionalWithPersona($id);
        if (!$profesional) {
            return response()->json(['message' => 'Profesional no encontrado'], 404);
        }
        return response()->json($profesional);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            // Pasamos todo el payload al servicio; el service separará persona/profesional
            $payload = $request->all();

            $profesional = $this->profesionalService->createProfesional($payload);
            return response()->json($profesional, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            // Permitimos que el payload contenga tanto datos de persona como datos del profesional
            $data = $request->all();

            $profesional = $this->profesionalService->updateProfesional($id, $data);
            return response()->json($profesional);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        if ($this->profesionalService->deleteProfesional($id)) {
            return response()->json(null, 204);
        }
        return response()->json(['message' => 'Profesional no encontrado'], 404);
    }
}