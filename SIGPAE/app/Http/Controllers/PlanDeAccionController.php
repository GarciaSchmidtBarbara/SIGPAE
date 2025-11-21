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

        return view('planDeAccion.crear-editar', $data + ['modo' => 'crear']);
    }
    
    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'tipo_plan' => 'required|in:Institucional,Individual,Grupal',
            'destinatario' => 'nullable|string|max:255',
            'descripcion' => 'required|string',
        ]);

        $this->planDeAccionService->crearPlanDeAccion($validatedData);

        return redirect()->route('planDeAccion.principal')
                         ->with('success', 'Plan de Acción creado con éxito.');
    }
    public function iniciarEdicion(int $id): View
    {
        $data = $this->planDeAccionService->datosParaFormulario($id);

        return view('planDeAccion.crear-editar', $data + [
            'modo' => 'editar',
            'planDeAccion' => $data['plan'],
        ]);
    }

    public function actualizar(Request $request, int $id): RedirectResponse
    {
        $validatedData = $request->validate([
            'tipo_plan' => 'required',
            'descripcion' => 'required|string',
        ]);

        $this->planDeAccionService->actualizarPlanDeAccion($id, $validatedData);

        return redirect()->route('planDeAccion.principal')
            ->with('success', 'Plan actualizado');
    }
   
}