<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Illuminate\View\View;
use App\Services\Interfaces\IntervencionServiceInterface;

use App\Models\Intervencion;
use App\Models\Alumno;
use App\Models\Profesional;
use App\Models\Aula;
use App\Models\PlanDeAccion;


class IntervencionController extends Controller
{
    public function __construct(IntervencionServiceInterface $intervencionService)
    {
        $this->service = $intervencionService;
    }

    public function vista (Request $request):View
    {
        $intervencionesFiltradas = $this->service->filtrar($request);
        $intervenciones = $this->service->formatearParaVista($intervencionesFiltradas);
        $tiposIntervencion = $this->service->obtenerTipos();
        $aulas = $this->service->obtenerAulas();

        return view('intervenciones.principal', compact('intervenciones', 'tiposIntervencion', 'aulas'));
    }



    public function crear()
    {
        $alumnos = Alumno::with('persona', 'aula')->get();
        $profesionales = Profesional::with('persona')->get();
        $aulas = $this->service->obtenerAulas();
        $planes = PlanDeAccion::all();
        $otrosAsistentes = $this->intervencionService->actualizarOtrosAsistentes($intervencion,json_decode($request->otros_asistentes_json, true)
        );

        return view('intervenciones.crear-editar', [
            'modo' => 'crear',
            'alumnos' => $alumnos,
            'profesionales' => $profesionales,
            'aulas' => $aulas,
            'planes' => $planes,
        ]);
    }

    public function guardar(Request $request)
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
            'compromisos' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'activo' => 'boolean',
            'tipo_intervencion' => 'required|string',
            'fk_id_profesional_generador' => 'required|integer|exists:profesionales,id_profesional',
            'plan_de_accion' => 'nullable|integer|exists:plan_de_accion,id_plan_de_accion',
    
        ]);

        try {
            $intervencion = $this->service->crear($data);
            return redirect()
                ->route('intervenciones.principal')
                ->with('success', 'Intervención creada exitosamente.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function iniciarEdicion(int $id): View
    {
        $intervencion = $this->service->buscarPorId($id);

        if (!$intervencion) {
            return redirect()
                ->route('intervenciones.principal')
                ->with('error', 'Intervención no encontrada.');
        }

        // === Alumnos seleccionados con datos completos para Alpine ===
        $alumnosSeleccionados = $intervencion->alumnos->map(function ($al) {
            $persona = $al->persona;
            return [
                'id' => $al->id_alumno,
                'nombre' => $persona->nombre,
                'apellido' => $persona->apellido,
                'dni' => $persona->dni,
                'curso'   => $al->aula?->descripcion,
                'aula_id' => $al->fk_id_aula,
            ];
        })->toArray();

        // Profesionales participantes
        $profesionalesSeleccionados = $intervencion->profesionales->map(function ($prof) {
            $persona = $prof->persona;
            return [
                'id' => $prof->id_profesional,
                'nombre' => $persona->nombre ?? null,
                'apellido' => $persona->apellido ?? null,
                'profesion' => $prof->profesion ?? 'N/A',
            ];
        })->toArray();

        // Aulas seleccionadas
        $aulasSeleccionadas = $intervencion->aulas->pluck('id_aula')->toArray();

        // Mapping completo de alumnos para Alpine
        $alumnosJson = $intervencion->alumnos->mapWithKeys(function ($al) {
            $persona = $al->persona;
            return [
                $al->id_alumno => [
                    'id' => $al->id_alumno,
                    'nombre' => $persona->nombre,
                    'apellido' => $persona->apellido,
                    'dni' => $persona->dni,
                    'curso' => $al->aula?->descripcion,
                    'aula_id' => $al->fk_id_aula,
                ]
            ];
        });

        // Colecciones completas para selects
        $alumnos = Alumno::with('persona', 'aula')->get();
        $profesionales = Profesional::with('persona')->get();
        $aulas = Aula::all();
        $planes = PlanDeAccion::all();

        return view('intervenciones.crear-editar', [
            'modo' => 'editar',
            'intervencion' => $intervencion,
            'alumnosSeleccionados' => $alumnosSeleccionados,
            'profesionalesSeleccionados' => $profesionalesSeleccionados,
            'aulasSeleccionadas' => $aulasSeleccionadas,
            'alumnos' => $alumnos,
            'aulas' => $aulas,
            'profesionales' => $profesionales,
            'planes' => $planes,
            'alumnosJson' => $alumnosJson,
        ]);
    }

    public function editar(Request $request, int $id)
    {
        $data = $request->validate([
            'fecha_hora_intervencion' => 'required|date',
            'lugar' => 'required|string|max:255',
            'modalidad' => 'required|string',
            'otra_modalidad' => 'nullable|string',
            'temas_tratados' => 'nullable|string',
            'compromisos' => 'nullable|string',
            'observaciones' => 'nullable|string',
        ]);

        try {
            $this->service->editar($id, $data);
            return redirect()
                ->route('intervenciones.principal')
                ->with('success', 'Intervención actualizada.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function eliminar(int $id)
    {
        $ok = $this->service->eliminar($id);

        return redirect()->route('intervenciones.principal')
                        ->with($ok ? 'success' : 'error', $ok ? 'Intervención eliminada.' : 'No se pudo eliminar.');
    }

    public function cambiarActivo(int $id)
    {
        $ok = $this->service->cambiarActivo($id);

        return redirect()->route('intervenciones.principal')
                        ->with($ok ? 'success' : 'error', $ok ? 'Intervención actualizada.' : 'No se pudo actualizar.');
    }

}
