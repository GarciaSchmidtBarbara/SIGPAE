<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\Interfaces\EventoServiceInterface;

class EventoController extends Controller
{
    protected EventoServiceInterface $eventoService;

    public function __construct(EventoServiceInterface $eventoService)
    {
        $this->eventoService = $eventoService;
    }

    public function index(): JsonResponse
    {
        $eventos = $this->eventoService->listarTodos();
        return response()->json($eventos);
    }

    public function show(int $id): JsonResponse
    {
        $evento = $this->eventoService->obtenerPorId($id);
        if (!$evento) {
            return response()->json(['message' => 'Evento no encontrado'], 404);
        }
        return response()->json($evento);
    }

    public function getEventosCalendario(Request $request): JsonResponse
    {
        $start = $request->query('start');
        $end = $request->query('end');

        if (!$start || !$end) {
            return response()->json(['error' => 'Se requieren parámetros start y end'], 400);
        }

        $eventos = $this->eventoService->obtenerEventosParaCalendario($start, $end);
        return response()->json($eventos);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'fecha_hora' => 'required|date',
                'lugar' => 'nullable|string|max:255',
                'tipo_evento' => 'required|string',
                'notas' => 'nullable|string',
            ]);

            $evento = $this->eventoService->crear($validated);
            return response()->json($evento, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'fecha_hora' => 'nullable|date',
                'lugar' => 'nullable|string|max:255',
                'tipo_evento' => 'nullable|string',
                'notas' => 'nullable|string',
            ]);

            $resultado = $this->eventoService->actualizar($id, $validated);
            if (!$resultado) {
                return response()->json(['message' => 'Evento no encontrado'], 404);
            }
            return response()->json(['message' => 'Evento actualizado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $resultado = $this->eventoService->eliminar($id);
        if (!$resultado) {
            return response()->json(['message' => 'Evento no encontrado'], 404);
        }
        return response()->json(['message' => 'Evento eliminado correctamente']);
    }
}
