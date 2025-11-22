<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\PlanDeAccionServiceInterface;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PlanDeAccionController extends Controller
{
    protected PlanDeAccionServiceInterface $planDeAccionService; 

    public function __construct(PlanDeAccionServiceInterface $planDeAccionService) 
    {
        $this->planDeAccionService = $planDeAccionService;
    }

    //vista planes filtrados
    public function vista(Request $request): View
    {
        $planesDeAccion = $this->planDeAccionService->filtrar($request);
        $aulas = $this->planDeAccionService->obtenerAulasParaFiltro();
        $tipos = $this->planDeAccionService->obtenerTipos(); 

        return view('planDeAccion.principal', compact('planesDeAccion', 'aulas', 'tipos'));
    }
    
    public function cambiarActivo(int $id): RedirectResponse
    {
        $resultado = $this->planDeAccionService->cambiarActivo($id);
        $mensaje = $resultado
            ? ['success' => 'El estado del Plan de Acción fue actualizado correctamente.']
            : ['error' => 'No pudo realizarse la actualización de estado del Plan de Acción.'];

        return redirect()->route('planDeAccion.principal')->with($mensaje);
    }
    
    public function iniciarCreacion(): View
    {
        $data = $this->planDeAccionService->datosParaFormulario();

        return view('planDeAccion.crear-editar', $data + [
            'modo' => 'crear',
            'alumnosSeleccionados' => []
        ]);
    }
    
    public function store(Request $request): RedirectResponse
    {
        $baseRules = [
            'tipo_plan' => 'required|in:INSTITUCIONAL,INDIVIDUAL,GRUPAL',
            'objetivos' => 'required|string',
            'acciones' => 'required|string',
            'observaciones' => 'nullable|string',
            'profesionales' => 'array',
            'profesionales.*' => 'integer|exists:profesionales,id_profesional',
            'aula' => 'nullable|integer|exists:aulas,id_aula',
            'alumnos' => ['nullable', 'array'],
            'alumnos.*' => ['nullable', 'integer', 'exists:alumnos,id_alumno'],
        ];

        // Validación preliminar para saber el tipo
        $validated = $request->validate($baseRules);
        $tipo = $validated['tipo_plan'];

        // VALIDACIÓN ADICIONAL SEGÚN TIPO
        if ($tipo === 'INDIVIDUAL') {
            if (empty($validated['alumnos']) || count($validated['alumnos']) < 1) {
                return back()->withErrors([
                    'alumnos' => 'Debe seleccionar un alumno para un plan individual.',
                ])->withInput();
            }
        }

        if ($tipo === 'GRUPAL') {
            $alumnos = $validated['alumnos'] ?? [];
            $aula = $validated['aula'] ?? null;

            if ((empty($alumnos) || count($alumnos) < 2) && !$aula) {
                return back()->withErrors([
                    'alumnos' => 'Debe seleccionar al menos dos alumnos o un aula completa para un plan grupal.',
                ])->withInput();
            }
            
        }

        if ($tipo === 'INSTITUCIONAL') {
            // nada extra → solo los generales
        }

        // Validar profesional generador
        if (!auth()->user()?->id_profesional) {
            return back()->with('error', 'No se puede crear el plan sin un profesional generador');
        }

        $validated['fk_id_profesional_generador'] = auth()->user()->id_profesional;

        // Crear plan
        $this->planDeAccionService->crear($validated);

        return redirect()->route('planDeAccion.principal')
                        ->with('success', 'Plan creado con éxito.');
    }

    public function iniciarEdicion(int $id): View
{
    $data = $this->planDeAccionService->datosParaFormulario($id);
    $plan = $data['plan'];

    // === Alumnos seleccionados con datos completos para Alpine ===
    $alumnosSeleccionados = $plan->alumnos->map(function ($al) {
        $persona = $al->persona;
        return [
            'id' => $al->id_alumno,
            'nombre' => $persona->nombre,
            'apellido' => $persona->apellido,
            'dni' => $persona->dni,
            'fecha_nacimiento' => $persona->fecha_nacimiento
                ? \Carbon\Carbon::parse($persona->fecha_nacimiento)->format('d/m/Y')
                : 'N/A',
            'nacionalidad' => $persona->nacionalidad ?? 'N/A',
            'domicilio' => $persona->domicilio ?? 'N/A',
            'edad' => $persona->fecha_nacimiento
                ? \Carbon\Carbon::parse($persona->fecha_nacimiento)->age
                : 'N/A',
            'curso'   => $al->aula?->descripcion,
            'aula_id' => $al->fk_id_aula,
        ];
    })->toArray();

    // === Profesional generador y participantes (datos completos para Alpine) ===
    $profesionalesSeleccionados = $plan->profesionalesParticipantes->map(function ($prof) {
        $persona = $prof->persona;
        return [
            'id' => $prof->id_profesional,
            'nombre' => $persona->nombre ?? null,
            'apellido' => $persona->apellido ?? null,
            'profesion' => $prof->profesion ?? 'N/A',
        ];
    })->toArray();

    // === Aulas seleccionadas (si aplica) ===
    $aulasSeleccionadas = $plan->aulas->pluck('id_aula')->toArray();

    // === Alumno individual (solo si el plan es INDIVIDUAL) ===
    $initialAlumnoId = null;
    $initialAlumnoInfo = null;
    if ($plan->tipo_plan->value === 'INDIVIDUAL') {
        $alumno = $plan->alumnos->first();
        if ($alumno) {
            $initialAlumnoId = $alumno->id_alumno;
            $initialAlumnoInfo = $alumnosSeleccionados[0] ?? null;
        }
    }

    return view('planDeAccion.crear-editar', [
        'modo' => 'editar',
        'planDeAccion' => $plan,

        // === Datos del plan cargado ===
        'alumnosSeleccionados' => $alumnosSeleccionados,
        'profesionalesSeleccionados' => $profesionalesSeleccionados,
        'aulasSeleccionadas' => $aulasSeleccionadas,

        // === Catálogos ===
        'alumnos' => $data['alumnos'],
        'aulas' => $data['aulas'],
        'profesionales' => $data['profesionales'],

        // === Alpine bindings ===
        'alumnosJson' => collect($alumnosSeleccionados)->keyBy('id'),
        'initialAlumnoId' => $initialAlumnoId,
        'initialAlumnoInfo' => $initialAlumnoInfo,
    ]);
}



    public function actualizar(Request $request, int $id): RedirectResponse
    {
        $validatedData = $request->validate([
            'tipo_plan' => 'required|in:INSTITUCIONAL,INDIVIDUAL,GRUPAL',
            'objetivos' => 'nullable|string',
            'acciones' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'alumnos' => 'array',
            'alumnos.*' => 'integer|exists:alumnos,id_alumno',
            'aula' => 'nullable|integer|exists:aulas,id_aula',
            'profesionales' => 'array',
            'profesionales.*' => 'integer|exists:profesionales,id_profesional',
        ]);

        $this->planDeAccionService->actualizar($id, $validatedData);

        return redirect()->route('planDeAccion.principal')
            ->with('success', 'Plan actualizado');
    }

    public function eliminar(int $id): RedirectResponse
    {
        $plan = $this->planDeAccionService->obtener($id);

        if (!$plan) {
            return redirect()->route('planDeAccion.principal')
                            ->with('error', 'El plan no existe.');
        }

        // Verificar si tiene intervenciones asociadas
        if ($plan->intervenciones && $plan->intervenciones->isNotEmpty()) {
            return redirect()->route('planDeAccion.principal')
                            ->with('error', 'No se puede eliminar el plan: tiene intervenciones asociadas.');
        }

        $this->planDeAccionService->eliminar($id);

        return redirect()->route('planDeAccion.principal')
                        ->with('success', 'Plan eliminado correctamente.');
    }

   
}