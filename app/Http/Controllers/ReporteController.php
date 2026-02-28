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
    public function principal()
{
    // 1. Datos para las tarjetas (KPIs)
    $totalAlumnos = Alumno::count();
    $totalIntervenciones = Intervencion::count();
    $totalPlanesDeAccion = PlanDeAccion::count();
    $totalPlanillas = Planilla::count();
    
    // Cambiamos 'activo' por el conteo total de profesionales ya que no existe la columna
    $usuariosActivos = Profesional::count(); 
    
    // Eventos del mes actual
    $eventosDelMes = Evento::whereMonth('fecha_hora', now()->month)
                           ->whereYear('fecha_hora', now()->year)
                           ->count();

    // 2. Gráfico de Evolución (PostgreSQL utiliza to_char y extract)
   // En tu ReporteController.php, actualiza la parte de evolucionIntervenciones:
$evolucionIntervenciones = Intervencion::select(
    DB::raw('count(*) as total'),
    DB::raw("TRIM(to_char(fecha_hora_intervencion, 'Month')) as mes"), // TRIM quita espacios
    DB::raw("extract(month from fecha_hora_intervencion) as mes_num")
)
->where('fecha_hora_intervencion', '>=', now()->subMonths(6))
->groupBy('mes', 'mes_num')
->orderBy('mes_num')
->get();
    // 3. Gráfico de Torta: Como 'activo' no existe, 
    // agrupamos por 'tipo_plan' que es el ENUM que tenés en Planes de Acción.
    // Esto mostrará cuántos hay de tipo 'INDIVIDUAL', 'GRUPAL', etc.
    $estadosPlanes = PlanDeAccion::select('tipo_plan as label', DB::raw('count(*) as total'))
                                 ->groupBy('tipo_plan')
                                 ->get();

    return view('reportes.principal', compact(
        'totalAlumnos',
        'totalIntervenciones',
        'totalPlanesDeAccion',
        'totalPlanillas',
        'usuariosActivos',
        'eventosDelMes',
        'evolucionIntervenciones',
        'estadosPlanes'
    ));
 }
}