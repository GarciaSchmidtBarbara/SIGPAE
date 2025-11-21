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
        $validatedData = $request->validate([
            // ajustar a los valores del enum (mayúsculas)
            'tipo_plan' => 'required|in:INSTITUCIONAL,INDIVIDUAL,GRUPAL',
            'objetivos' => 'nullable|string',
            'acciones' => 'nullable|string',
            'observaciones' => 'nullable|string',
            // ahora el formulario envía "alumnos[]" en todos los casos
            'alumnos' => 'array',
            'alumnos.*' => 'integer|exists:alumnos,id_alumno',
            'aula' => 'nullable|integer|exists:aulas,id_aula',
            'profesionales' => 'array',
            'profesionales.*' => 'integer|exists:profesionales,id_profesional',
        ]);
        // ⚠️ Validar antes de crear
        if (!auth()->user() || !auth()->user()->id_profesional) {
            return redirect()->back()->with(
                'error',
                'No se puede crear el plan: el usuario no tiene un profesional asociado.'
            );
        }

        // Inyectar el profesional generador automáticamente
        $validatedData['fk_id_profesional_generador'] = auth()->user()->id_profesional;

        $this->planDeAccionService->crear($validatedData);

        return redirect()->route('planDeAccion.principal')
                         ->with('success', 'Plan de Acción creado con éxito.');
    }

    public function iniciarEdicion(int $id): View
    {
        $data = $this->planDeAccionService->datosParaFormulario($id);

        return view('planDeAccion.crear-editar', $data + [
            'modo' => 'editar',
            'planDeAccion' => $data['plan'],
            'alumnosSeleccionados' => $data['plan']->alumnos ?? []
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
   
}