<?php
namespace App\Http\Controllers;
use App\Modules\Planes\Models\PlanDeAccion;

class PlanDeAccionController extends Controller{
  public function show($id)  {
      $planDeAccion = PlanDeAccion::findOrFail($id);
      
      // Define el modo de visualización
      $modoVisualizacion = ($planDeAccion->estado === 'Cerrado') ? 'ver' : 'editar';
      
      // Determina si los campos deben estar deshabilitados
      $esCerrado = ($planDeAccion->estado === 'Cerrado');

      return view('plan-de-accion', compact('planDeAccion', 'esCerrado'));
  }
}