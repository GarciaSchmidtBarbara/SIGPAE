<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\PlanDeAccionServiceInterface;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PlanDeAccionController extends Controller
{
    // Cambiar el tipo inyectado al de la Interfaz
    protected PlanDeAccionServiceInterface $planDeAccionService; 

    // Aquí se inyecta la Interfaz
    public function __construct(PlanDeAccionServiceInterface $planDeAccionService) 
    {
        $this->planDeAccionService = $planDeAccionService;
    }

    public function principal(Request $request): View
    {
        $data = $this->planDeAccionService->obtenerPlanesParaPrincipal($request);
        
        return view('planDeAccion.principal', [
            'planesDeAccion' => $data['planesDeAccion'],
            'aulas' => $data['aulas'],
        ]);
    }
    
    public function cambiarActivo(int $id): RedirectResponse
    {
        if ($this->planDeAccionService->toggleActivo($id)) {
            return redirect()->route('planDeAccion.principal')
                             ->with('success', 'El estado del Plan de Acción ha sido cambiado con éxito.');
        }

        return redirect()->route('planDeAccion.principal')
                         ->with('error', 'No se pudo cambiar el estado del Plan de Acción.');
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