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
        
        return view('planDeAccion.principal', $data);
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
        return view('planDeAccion.crear-editar'); 
    }
    
    // ... otros métodos CRUD (store, edit, update, destroy)
}