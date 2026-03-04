<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alumno;
use App\Models\Intervencion;
use App\Models\PlanDeAccion;
use App\Models\Planilla;
use App\Models\Profesional;
use App\Models\Evento; // Nuevo
use Carbon\Carbon;     // Para manejar fechas
use Illuminate\Support\Facades\DB;
use App\Models\Reporte;

class ReporteController extends Controller
{
public function principal(Request $request)
{
    // 3. Filtro interactivo: si no mandan nada, por defecto son 6 meses
    $mesesFiltro = $request->get('meses', 6);

    $totalAlumnos = Alumno::count();
    $totalIntervenciones = Intervencion::count();
    $totalPlanesDeAccion = PlanDeAccion::count();
    $usuariosActivos = Profesional::count(); 
    
    $eventosDelMes = Evento::whereMonth('fecha_hora', now()->month)
                           ->whereYear('fecha_hora', now()->year)
                           ->count();

    // Usamos el MODELO ahora para traer los datos y los meses en español
    $evolucionIntervenciones = Reporte::getEvolucionIntervenciones($mesesFiltro);

    $estadosPlanes = PlanDeAccion::select('tipo_plan as label', DB::raw('count(*) as total'))
                                 ->groupBy('tipo_plan')
                                 ->get();

    return view('reportes.principal', compact(
        'totalAlumnos', 'totalIntervenciones', 'totalPlanesDeAccion', 
        'usuariosActivos', 'eventosDelMes', 'evolucionIntervenciones', 
        'estadosPlanes', 'mesesFiltro'
    ));
}
}