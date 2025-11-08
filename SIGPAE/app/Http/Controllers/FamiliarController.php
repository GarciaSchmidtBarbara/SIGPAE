<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\Interfaces\FamiliarServiceInterface;


class FamiliarController extends Controller
{
    protected $familiarService;

    public function __construct(FamiliarServiceInterface $familiarService)
    {
        $this->familiarService = $familiarService;
    }

    public function index(): JsonResponse
    {
        $familiares = $this->familiaresService->getAllFamiliaresWithPersona();
        return response()->json($familiares);
    }

    public function show(int $id): JsonResponse
    {
        $familiares = $this->familiarService->getFamiliarWithPersona($id);
        if (!$familiar) {
            return response()->json(['message' => 'Familiar no encontrado'], 404);
        }
        return response()->json($familiar);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $payload = $request->all();

            $familiar = $this->familiarService->createFamiliar($payload);
            return response()->json($familiar, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $data = $request->all();
            $familiar = $this->familiarService->updateFamiliar($id, $data);
            return response()->json($familiar);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        if ($this->familiarService->deleteFamiliar($id)) {
            return response()->json(null, 204);
        }
        return response()->json(['message' => 'Familiar no encontrado'], 404);
    }
}