<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Services\Interfaces\EventoServiceInterface;
use App\Services\Interfaces\ProfesionalServiceInterface;
use App\Enums\TipoEvento;

class EventoController extends Controller
{
    protected EventoServiceInterface $eventoService;
    protected ProfesionalServiceInterface $profesionalService;

    public function __construct(
        EventoServiceInterface $eventoService,
        ProfesionalServiceInterface $profesionalService
    ) {
        $this->eventoService = $eventoService;
        $this->profesionalService = $profesionalService;
    }

    public function vista(Request $request)
    {
        $filters = $request->only('tipo_evento');
        $eventos = $this->eventoService->listarTodos($filters);

        $tiposEvento = TipoEvento::cases();

        return view('eventos.principal', compact('eventos', 'tiposEvento'));
    }

    public function actualizarConfirmacion(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'confirmado' => 'required|boolean',
            ]);

            $profesionalId = auth()->user()->getAuthIdentifier();
            $resultado = $this->eventoService->actualizarConfirmacion($id, $profesionalId, $validated['confirmado']);

            if ($resultado) {
                return response()->json(['success' => true, 'message' => 'Confirmación actualizada']);
            }

            return response()->json(['success' => false, 'message' => 'No está invitado a este evento'], 403);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function crear()
    {
        $profesionalesDisponibles = $this->profesionalService->getAllProfesionalesWithPersona();
        $datos = $this->eventoService->obtenerDatosVistaEvento(0);
        $cursos = $datos['cursos'] ?? collect();
        
        return view('eventos.crear-editar', compact('profesionalesDisponibles', 'cursos'));
    }

    public function ver(int $id)
    {
        $datos = $this->eventoService->obtenerDatosVistaEvento($id);
        
        if (empty($datos)) {
            return redirect()->route('eventos.principal')
                ->with('error', 'Evento no encontrado');
        }

        $profesionalesDisponibles = $this->profesionalService->getAllProfesionalesWithPersona();

        return view('eventos.crear-editar', [
            'evento' => $datos['evento'],
            'profesionalesDisponibles' => $profesionalesDisponibles,
            'cursos' => $datos['cursos'],
            'profesionalesEvento' => $datos['profesionalesEvento'],
            'alumnosEvento' => $datos['alumnosEvento'],
            'cursosEvento' => $datos['cursosEvento'],
        ]);
    }

    public function editar(int $id)
    {
        // El método editar es idéntico a ver, ya que usan la misma vista
        return $this->ver($id);
    }

    public function guardar(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tipo_evento' => 'required|string',
            'fecha_hora' => 'required|date',
            'lugar' => 'required|string|max:255',
            'notas' => 'nullable|string',
            'periodo_recordatorio' => 'nullable|integer|min:1',
            'profesionales' => 'nullable|array',
            'cursos' => 'nullable|array',
            'alumnos' => 'nullable|array',
        ], [
            'tipo_evento.required' => 'Debe seleccionar un tipo de evento',
            'fecha_hora.required' => 'Debe ingresar fecha y hora',
            'lugar.required' => 'Debe ingresar el lugar',
        ]);

        try {
            $evento = $this->eventoService->crearConParticipantes($validated);

            return redirect()->route('eventos.principal')
                ->with('success', 'Evento creado correctamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear evento: ' . $e->getMessage());
        }
    }

    public function actualizar(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'tipo_evento' => 'required|string',
            'fecha_hora' => 'required|date',
            'lugar' => 'required|string|max:255',
            'notas' => 'nullable|string',
            'periodo_recordatorio' => 'nullable|integer|min:1',
            'profesionales' => 'nullable|array',
            'cursos' => 'nullable|array',
            'alumnos' => 'nullable|array',
        ], [
            'tipo_evento.required' => 'Debe seleccionar un tipo de evento',
            'fecha_hora.required' => 'Debe ingresar fecha y hora',
            'lugar.required' => 'Debe ingresar el lugar',
        ]);

        try {
            $this->eventoService->actualizarConParticipantes($id, $validated);

            return redirect()->route('eventos.principal')
                ->with('success', 'Evento actualizado correctamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar evento: ' . $e->getMessage());
        }
    }

    public function crearDerivacion()
    {
        return view('eventos.crear-derivacion');
    }

    public function guardarDerivacion(Request $request): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'fecha' => 'required|date',
                'lugar' => 'required|string|max:255',
                'profesional_tratante' => 'nullable|string|max:255',
                'periodo_recordatorio' => 'nullable|integer|min:1',
                'notas' => 'nullable|string',
                'alumnos' => 'required|array|min:1',
            ], [
                'lugar.required' => 'Debe ingresar el lugar',
                'fecha.required' => 'Debe ingresar la fecha',
                'alumnos.required' => 'Debe agregar al menos un participante',
                'alumnos.min' => 'Debe agregar al menos un participante',
            ]);

            $this->eventoService->crearDerivacionExterna($validated);

            return redirect()->route('eventos.principal')
                ->with('success', 'Derivación externa creada correctamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear derivación: ' . $e->getMessage());
        }
    }

    public function editarDerivacion(int $id)
    {
        $evento = $this->eventoService->obtenerPorId($id);

        if (!$evento || $evento->tipo_evento?->value !== 'DERIVACION_EXTERNA') {
            return redirect()->route('eventos.principal')
                ->with('error', 'Derivación no encontrada');
        }

        $datos = $this->eventoService->obtenerDatosVistaEvento($id);
        $alumnosEvento = $datos['alumnosEvento'] ?? [];

        return view('eventos.crear-derivacion', compact('evento', 'alumnosEvento'));
    }

    public function actualizarDerivacion(Request $request, int $id): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'fecha' => 'required|date',
                'lugar' => 'required|string|max:255',
                'profesional_tratante' => 'nullable|string|max:255',
                'periodo_recordatorio' => 'nullable|integer|min:1',
                'notas' => 'nullable|string',
                'alumnos' => 'required|array|min:1',
            ], [
                'lugar.required' => 'Debe ingresar el lugar',
                'fecha.required' => 'Debe ingresar la fecha',
                'alumnos.required' => 'Debe agregar al menos un participante',
                'alumnos.min' => 'Debe agregar al menos un participante',
            ]);

            $this->eventoService->actualizarDerivacionExterna($id, $validated);

            return redirect()->route('eventos.principal')
                ->with('success', 'Derivación externa actualizada correctamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar derivación: ' . $e->getMessage());
        }
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

    public function destroy(int $id): RedirectResponse
    {
        try {
            $resultado = $this->eventoService->eliminar($id);
            
            if (!$resultado) {
                return redirect()->back()->with('error', 'Evento no encontrado');
            }
            
            return redirect()->route('eventos.principal')
                ->with('success', 'Evento eliminado correctamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar evento: ' . $e->getMessage());
        }
    }

    /**
     * Establece periodo_recordatorio = 0 para detener los recordatorios de una derivación externa.
     */
    public function dejarDeRecordar(int $id): JsonResponse
    {
        $resultado = $this->eventoService->dejarDeRecordar($id);

        if (!$resultado) {
            return response()->json(['success' => false, 'message' => 'Evento no encontrado'], 404);
        }

        return response()->json(['success' => true]);
    }
}
