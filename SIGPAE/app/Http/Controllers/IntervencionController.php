<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\IntervencionServiceInterface;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

use App\Models\Intervencion;


class IntervencionController extends Controller
{
    protected IntervencionServiceInterface $intervencionService; 

    public function __construct(IntervencionServiceInterface $intervencionService)
    {
        $this->intervencionService = $intervencionService;
    }

    //vista intervenciones filtradas
    public function vista (Request $request):View
    {
        $intervencionesFiltradas = $this->intervencionService->filtrar($request);
        $intervenciones = $this->intervencionService->formatearParaVista($intervencionesFiltradas);
        $tiposIntervencion = $this->intervencionService->obtenerTipos();
        $aulas = $this->intervencionService->obtenerAulas();

        return view('intervenciones.principal', compact('intervenciones', 'tiposIntervencion', 'aulas'));
    }

    //metodos de creacion y almacenamiento
    public function iniciarCreacion(): View
    {
        $data = $this->intervencionService->datosParaFormulario();
        return view('intervenciones.crear-editar', $data + [
            'modo' => 'crear',
            'otrosAsistentes' => [],
            'intervencion' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // 1. Asegurar que el ID del generador esté en el Request (siempre antes de la validación)
        $request->merge([
            'fk_id_profesional_generador' => auth()->user()->id_profesional ?? auth()->id(),
        ]);

        $data = $request->validate([
            'fecha_hora_intervencion' => 'required|date',
            'lugar' => 'required|string|max:255',
            'modalidad' => 'required|string',
            'otra_modalidad' => 'nullable|string',
            'temas_tratados' => 'nullable|string',
            'compromisos' => 'required|string',
            'observaciones' => 'nullable|string',
            'activo' => 'boolean',
            'tipo_intervencion' => 'required|string',
            'alumnos' => 'nullable|array',
            'alumnos.*' => 'integer|exists:alumnos,id_alumno',
            'fk_id_profesional_generador' => 'required|integer|exists:profesionales,id_profesional',
            'plan_de_accion' => 'nullable|integer|exists:plan_de_accion,id_plan_de_accion',
        ]);
        $data['fecha_hora_intervencion'] = $request->input('fecha_hora_intervencion') . ' ' . $request->input('hora_intervencion');

        try {
            $intervencion = $this->intervencionService->crear($data);

            //guardar otros asistentes si vienen en el request
            if ($request->filled('otros_asistentes_json')) {
                $otrosAsistentes = json_decode($request->otros_asistentes_json, true);
                $this->intervencionService->guardarOtrosAsistentes($intervencion, $otrosAsistentes);
            }

            return redirect()
                ->route('intervenciones.principal')
                ->with('success', 'Intervención creada exitosamente.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    //metodos de edicion y almacenamiento
    public function iniciarEdicion(int $id): View
    {
        $data = $this->intervencionService->datosParaFormulario($id);

        if (!$data['intervencion']) {
            return redirect()
                ->route('intervenciones.principal')
                ->with('error', 'Intervención no encontrada.');
        }

        return view('intervenciones.crear-editar', $data + [
            'modo' => 'editar',
        ]);
    }

    public function editar(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'fecha_hora_intervencion' => 'required|date',
            'hora_intervencion' => 'required',
            'lugar' => 'required|string|max:255',
            'modalidad' => 'required|string',
            'otra_modalidad' => 'nullable|string',
            'temas_tratados' => 'nullable|string',
            'compromisos' => 'required|string',
            'observaciones' => 'nullable|string',
            'alumnos' => 'nullable|array',
            'alumnos.*' => 'integer|exists:alumnos,id_alumno',
            'aulas' => 'nullable|array',
            'aulas.*' => 'integer|exists:aulas,id_aula',
            'profesionales' => 'nullable|array',
            'profesionales.*' => 'integer|exists:profesionales,id_profesional',
        ]);
        $data['fecha_hora_intervencion'] = $request->input('fecha_hora_intervencion') . ' ' . $request->input('hora_intervencion');

        try {
            $intervencion= $this->intervencionService->actualizar($id, $data);

            if ($request->filled('otros_asistentes_json')) {
                $otrosAsistentes = json_decode($request->otros_asistentes_json, true);
                $this->intervencionService->guardarOtrosAsistentes($intervencion, $otrosAsistentes);
            }
            
            return redirect()
                ->route('intervenciones.principal')
                ->with('success', 'Intervención actualizada.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function eliminar(int $id)
    {
        $ok = $this->intervencionService->eliminar($id);

        return redirect()->route('intervenciones.principal')
                        ->with($ok ? 'success' : 'error', $ok ? 'Intervención eliminada.' : 'No se pudo eliminar.');
    }

    public function cambiarActivo(int $id): RedirectResponse
    {
        $ok = $this->intervencionService->cambiarActivo($id);

        return redirect()->route('intervenciones.principal')
                        ->with($ok ? 'success' : 'error', $ok ? 'Intervención actualizada.' : 'No se pudo actualizar.');
    }
}
