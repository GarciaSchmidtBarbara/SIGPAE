<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Services\Interfaces\EventoServiceInterface;
use App\Services\Interfaces\ProfesionalServiceInterface;
use App\Models\Aula;
use App\Models\Alumno;

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

    public function vista()
    {
        $eventos = $this->eventoService->listarTodos();
        $eventos->load(['profesionalCreador.persona', 'esInvitadoA']);
        return view('eventos.principal', compact('eventos'));
    }

    public function actualizarConfirmacion(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'confirmado' => 'required|boolean',
            ]);

            $evento = $this->eventoService->obtenerPorId($id);
            if (!$evento) {
                return response()->json(['message' => 'Evento no encontrado'], 404);
            }

            // Actualizar confirmación del profesional autenticado en este evento
            $profesionalId = auth()->user()->getAuthIdentifier();
            $invitacion = $evento->esInvitadoA()->where('fk_id_profesional', $profesionalId)->first();
            
            if ($invitacion) {
                $invitacion->confirmacion = $validated['confirmado'];
                $invitacion->save();
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
        $cursos = Aula::all();
        
        return view('eventos.crear-editar', compact('profesionalesDisponibles', 'cursos'));
    }

    public function ver(int $id)
    {
        $evento = $this->eventoService->obtenerPorId($id);
        
        if (!$evento) {
            return redirect()->route('eventos.principal')
                ->with('error', 'Evento no encontrado');
        }

        $profesionalesDisponibles = $this->profesionalService->getAllProfesionalesWithPersona();
        $cursos = Aula::all();
        
        // Cargar relaciones del evento
        $profesionalesEvento = $evento->esInvitadoA()->with('profesional.persona')->get()->map(function($inv) {
            return [
                'id' => $inv->profesional->id_profesional,
                'invitado' => true,
                'confirmado' => $inv->confirmacion ?? false,
                'asistio' => $inv->asistio ?? false
            ];
        })->toArray();

        $alumnosEvento = $evento->alumnos()->with('persona', 'aula')->get()->toArray();
        $cursosEvento = $evento->aulas()->pluck('id_aula')->toArray();

        return view('eventos.crear-editar', compact(
            'evento',
            'profesionalesDisponibles',
            'cursos',
            'profesionalesEvento',
            'alumnosEvento',
            'cursosEvento'
        ));
    }

    public function guardar(Request $request): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'tipo_evento' => 'required|string',
                'fecha_hora' => 'required|date',
                'lugar' => 'nullable|string|max:255',
                'notas' => 'nullable|string',
                'profesionales' => 'nullable|array',
                'cursos' => 'nullable|array',
                'alumnos' => 'nullable|array',
            ], [
                'tipo_evento.required' => 'Debe seleccionar un tipo de evento',
                'fecha_hora.required' => 'Debe ingresar fecha y hora',
            ]);

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
        try {
            $validated = $request->validate([
                'tipo_evento' => 'required|string',
                'fecha_hora' => 'required|date',
                'lugar' => 'nullable|string|max:255',
                'notas' => 'nullable|string',
                'profesionales' => 'nullable|array',
                'cursos' => 'nullable|array',
                'alumnos' => 'nullable|array',
            ]);

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
        $profesionalesDisponibles = $this->profesionalService->getAllProfesionalesWithPersona();
        return view('eventos.crear-derivacion', compact('profesionalesDisponibles'));
    }

    public function guardarDerivacion(Request $request): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'descripcion_externa' => 'required|string',
                'fecha' => 'nullable|date',
                'lugar' => 'nullable|string|max:255',
                'profesional_id' => 'nullable|exists:profesionales,id_profesional',
                'periodo_recordatorio' => 'nullable|integer|min:1',
                'notas' => 'nullable|string',
                'alumnos' => 'nullable|array',
            ], [
                'descripcion_externa.required' => 'Debe ingresar una descripción',
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
}
