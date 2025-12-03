<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Planilla;

class PlanillaController extends Controller
{
    public function index(Request $request)
    {
        // 1. Iniciamos la consulta
        $query = Planilla::query();  

        if ($request->filled('buscar')) {
            $busqueda = trim($request->buscar);

            $query->where(function($q) use ($busqueda) {
                $q->where('nombre_planilla', 'ILIKE', "%{$busqueda}%")
                  ->orWhere('tipo_planilla', 'ILIKE', "%{$busqueda}%")
                
                  ->orWhereRaw("datos_planilla->>'escuela' ILIKE ?", ["%{$busqueda}%"]) 
                  ->orWhereRaw("datos_planilla->>'grado' ILIKE ?", ["%{$busqueda}%"]);
            });
        }

        // 3. Ordenamos: Las más nuevas primero (la "pila")
        $planillas = $query->orderBy('created_at', 'desc')
                           ->paginate(10)
                           ->withQueryString();

        return view('planillas.principal', compact('planillas'));
    }

    // 1. Mostrar el formulario
    public function crearActaIndisciplinario()
    {
        // Datos falsos para que la tabla no se vea vacía al inicio
        $datos_falsos = [
            ['cargo' => 'EI', 'nombre' =>'Psp. Sandra Yamzon','asistio' => false],
            ['cargo' => 'EI', 'nombre' =>'Ps. Claudio Casele','asistio' => true],
            ['cargo' => 'EI', 'nombre' =>'Ps. Mercedez Carreño','asistio' => true],
            ['cargo' => 'EI', 'nombre' =>'As. Hugo Gonzales','asistio' => true],
            ['cargo' => 'EI', 'nombre' =>'Psp. Lucía Iglesia','asistio' => true],
            ['cargo' => 'EI', 'nombre' =>'Ae. Pablo Brizuela','asistio' => true],
            ['cargo' => 'EI', 'nombre' =>'Pga. Mayra Brito','asistio' => true],
            ['cargo' => 'Director', 'nombre' => '', 'asistio' => false],
            ['cargo' => 'Vicedirector', 'nombre' => '', 'asistio' => false],
        ];

        return view('planillas.acta-equipo-indisciplinario', ['personal' => $datos_falsos]);
    }

    // 2. Guardar el formulario
    public function guardarActaIndisciplinario(Request $request)
    {
        $participantes = json_decode($request->participantes_json, true);

        // Preparamos el contenido
        $contenido = [
            'grado'           => $request->grado,
            'fecha'           => $request->fecha,
            'hora'            => $request->hora,
            'participantes'   => $participantes,
            'temario'         => $request->temario,
            'acuerdo'         => $request->acuerdo,
            'observaciones'   => $request->observaciones,
            'proxima_reunion' => $request->proxima_reunion,
        ];

        // Guardamos en BD
        Planilla::create([
            'tipo_planilla'   => 'ACTA REUNIÓN EQUIPO INTERDISCIPLINARIO- equipo directivo',
            'nombre_planilla' => 'Acta EI Directivo - ' . $request->fecha,
            'anio'            => date('Y'),
            'datos_planilla'  => $contenido
        ]);

        return redirect()
            ->route('planillas.acta-equipo-indisciplinario.create')
            ->with('success', '¡Acta guardada correctamente!');
    }

    public function crearActaReunionTrabajo()
    {
        // Lista SIN Directores (solo el equipo interdisciplinario)
        $datos_falsos = [
            ['cargo' => 'EI', 'nombre' =>'Psp. Sandra Yamzon','asistio' => false],
            ['cargo' => 'EI', 'nombre' =>'Ps. Claudio Casele','asistio' => true],
            ['cargo' => 'EI', 'nombre' =>'Ps. Mercedez Carreño','asistio' => true],
            ['cargo' => 'EI', 'nombre' =>'As. Hugo Gonzales','asistio' => true],
            ['cargo' => 'EI', 'nombre' =>'Psp. Lucía Iglesia','asistio' => true],
            ['cargo' => 'EI', 'nombre' =>'Ae. Pablo Brizuela','asistio' => true],
            ['cargo' => 'EI', 'nombre' =>'Pga. Mayra Brito','asistio' => true],
        ];

        return view('planillas.acta-reunion-trabajo', ['personal' => $datos_falsos]);
    }

    // 2. Guardar (POST)
    public function guardarActaReunionTrabajo(Request $request)
    {
        $participantes = json_decode($request->participantes_json, true);

        $contenido = [
            'grado'           => $request->grado,
            'fecha'           => $request->fecha,
            'hora'            => $request->hora,
            'participantes'   => $participantes,
            'temario'         => $request->temario,
            'acuerdo'         => $request->acuerdo,
            'observaciones'   => $request->observaciones,
            'proxima_reunion' => $request->proxima_reunion,
        ];

        Planilla::create([
           
            'tipo_planilla'   => 'ACTA REUNIÓN EQUIPO INTERDISCIPLINARIO', 
            
            'nombre_planilla' => 'Acta EI - ' . $request->fecha,
            'anio'            => date('Y'),
            'datos_planilla'  => $contenido
        ]);
        return redirect()
            ->route('planillas.acta-reunion-trabajo.create')
            ->with('success', '¡Acta guardada correctamente!');
    }

    // --- NUEVA: ACTA BANDA (EI + DIRECTIVOS + DOCENTES) ---

    // 1. Mostrar (GET)
    public function crearActaBanda()
    {
        
        $datos_falsos = [
            // --- EQUIPO EI ---
            ['cargo' => 'EI', 'nombre' =>'Psp. Sandra Yamzon','asistio' => false],
            ['cargo' => 'EI', 'nombre' =>'Ps. Claudio Casele','asistio' => true],
            ['cargo' => 'EI', 'nombre' =>'Ps. Mercedes Carreño','asistio' => true],
            ['cargo' => 'EI', 'nombre' =>'As. Hugo González','asistio' => true], // Corregido tilde
            ['cargo' => 'EI', 'nombre' =>'Psp. Lucía','asistio' => true],
            ['cargo' => 'EI', 'nombre' =>'Ae. Pablo Brizuela','asistio' => true],
            ['cargo' => 'EI', 'nombre' =>'Fga. Mayra Brito','asistio' => true], // Corregido cargo Fga.
            
            // --- DIRECTIVOS los dejamos precargados ---
            ['cargo' => 'Directora', 'nombre' => 'Prof. Gabriela Campetti', 'asistio' => false],
            ['cargo' => 'Vicedirector', 'nombre' => 'Prof. Gustavo Lezcano', 'asistio' => false],

            // --- DOCENTES (Filas vacías para completar a mano) ---
            ['cargo' => 'Docente', 'nombre' => '', 'asistio' => false],
            ['cargo' => 'Docente', 'nombre' => '', 'asistio' => false],
            ['cargo' => 'Docente', 'nombre' => '', 'asistio' => false],
        ];

        return view('planillas.acta-reuniones-banda', ['personal' => $datos_falsos]);
    }

    // 2. Guardar (POST)
    public function guardarActaBanda(Request $request)
    {
        $participantes = json_decode($request->participantes_json, true);

        $contenido = [
            'grado'           => $request->grado,
            'fecha'           => $request->fecha,
            'hora'            => $request->hora,
            'participantes'   => $participantes,
            'temario'         => $request->temario,
            'acuerdo'         => $request->acuerdo,
            'observaciones'   => $request->observaciones,
            'proxima_reunion' => $request->proxima_reunion,
        ];

        Planilla::create([
            
            'tipo_planilla'   => 'ACTA REUNIÓN DE TRABAJO - EQUIPO DIRECTIVO - EI - DOCENTES (BANDA)', 
            
            'nombre_planilla' => 'Acta Banda - ' . $request->fecha,
            'anio'            => date('Y'),
            'datos_planilla'  => $contenido
        ]);

        return redirect()
            ->route('planillas.acta-reuniones-banda.create')
            ->with('success', '¡Acta (Banda) guardada correctamente!');
    }

    // --- PLANILLA MEDIAL ---

    // 1. Mostrar
    public function crearPlanillaMedial()
    {
        // No enviamos datos falsos porque empieza vacía
        return view('planillas.planilla-medial');
    }

    // 2. Guardar
    public function guardarPlanillaMedial(Request $request)
    {
      
        $filas_tabla = json_decode($request->medial_json, true);

        $contenido = [
            'anio_escolar' => $request->anio,
            'fecha'        => $request->fecha,
            'escuela'      => $request->escuela,
            'tabla_medial' => $filas_tabla, // Aquí van todas las filas que cargó el usuario
        ];

        Planilla::create([
            'tipo_planilla'   => 'PLANILLA MEDIAL',
            'nombre_planilla' => 'Medial Esc. ' . $request->escuela . ' - ' . $request->fecha,
            'anio'            => $request->anio,
            'datos_planilla'  => $contenido
        ]);

        return redirect()
            ->route('planillas.planilla-medial.create')
            ->with('success', '¡Planilla Medial guardada correctamente!');
    }

    // --- PLANILLA FINAL ---

    // 1. Mostrar
    public function crearPlanillaFinal()
    {
        return view('planillas.planilla-final');
    }

    // 2. Guardar
    public function guardarPlanillaFinal(Request $request)
    {
        $filas_tabla = json_decode($request->medial_json, true);

        $contenido = [
            'anio_escolar' => $request->anio,
            'fecha'        => $request->fecha,
            'escuela'      => $request->escuela,
            'tabla_final'  => $filas_tabla,
        ];

        Planilla::create([
            'tipo_planilla'   => 'PLANILLA FINAL', // <--- Nombre Diferente
            'nombre_planilla' => 'Final Esc. ' . $request->escuela . ' - ' . $request->fecha,
            'anio'            => $request->anio,
            'datos_planilla'  => $contenido
        ]);

        return redirect()
            ->route('planillas.planilla-final.create')
            ->with('success', '¡Planilla Final guardada correctamente!');
    }

    // --- ZONA DE PAPELERA DE RECICLAJE ---

    // 1. Mandar a la papelera (Soft Delete)
    public function eliminar($id)
    {
        $planilla = Planilla::findOrFail($id);
        $planilla->delete(); // Al tener SoftDeletes, esto no borra, solo "oculta"

        return redirect()->route('planillas.principal')
            ->with('success', 'La planilla se movió a la papelera.');
    }

    // 2. Ver la Papelera
    public function verPapelera()
    {
        // Traemos SOLO las que fueron borradas
        $borradas = Planilla::onlyTrashed()->orderBy('deleted_at', 'desc')->get();

        return view('planillas.papelera', compact('borradas'));
    }

    // 3. Restaurar (Sacar de la papelera)
    public function restaurar($id)
    {
        $planilla = Planilla::withTrashed()->findOrFail($id);
        $planilla->restore(); // ¡Magia! Vuelve a estar activa

        return redirect()->route('planillas.papelera')
            ->with('success', 'Planilla restaurada correctamente.');
    }

    // eliminado definitivo
    public function forzarEliminacion($id)
    {
        $planilla = Planilla::withTrashed()->findOrFail($id);
        $planilla->forceDelete(); 

        return redirect()->route('planillas.papelera')
            ->with('error', 'Planilla eliminada definitivamente.'); // Usamos rojo (error) para avisar
    }

    // --- LÓGICA DE EDICIÓN ---

    // 1. SEMÁFORO DE EDICIÓN
    public function editar($id)
    {
        $planilla = Planilla::findOrFail($id);
        $datos = $planilla->datos_planilla; // El JSON con toda la info

        // Según el tipo, elegimos qué archivo abrir
        // NOTA: Usamos los mismos archivos de 'crear', pero les pasamos la variable $planilla
        
        switch ($planilla->tipo_planilla) {
            case 'ACTA REUNIÓN EQUIPO INTERDISCIPLINARIO- equipo directivo':
                return view('planillas.acta-equipo-indisciplinario', compact('planilla'));

            case 'ACTA REUNIÓN EQUIPO INTERDISCIPLINARIO':
                return view('planillas.acta-reunion-trabajo', compact('planilla'));

            case 'ACTA REUNIÓN DE TRABAJO - EQUIPO DIRECTIVO - EI - DOCENTES (BANDA)':
                return view('planillas.acta-reuniones-banda', compact('planilla'));

            case 'PLANILLA MEDIAL':
                return view('planillas.planilla-medial', compact('planilla'));

            case 'PLANILLA FINAL':
                return view('planillas.planilla-final', compact('planilla'));

            default:
                return redirect()->back()->with('error', 'Tipo de planilla desconocido.');
        }
    }


    public function actualizar(Request $request, $id)
    {
        $planilla = Planilla::findOrFail($id);

        // Dependiendo del tipo, reconstruimos el JSON
        // (Aquí simplificamos: Si es medial usa una lógica, si es acta usa otra)
        
        $contenido = [];

        if (str_contains($planilla->tipo_planilla, 'PLANILLA')) {
            // Lógica para MEDIAL y FINAL (tienen tabla_medial)
            $tabla = json_decode($request->medial_json, true);
            $contenido = [
                'anio_escolar' => $request->anio,
                'fecha'        => $request->fecha,
                'escuela'      => $request->escuela,
                'tabla_medial' => $tabla, // o tabla_final, da igual el nombre interno
            ];
        } else {
        
            $participantes = json_decode($request->participantes_json, true);
            $contenido = [
                'grado'           => $request->grado,
                'fecha'           => $request->fecha,
                'hora'            => $request->hora,
                'participantes'   => $participantes,
                'temario'         => $request->temario,
                'acuerdo'         => $request->acuerdo,
                'observaciones'   => $request->observaciones,
                'proxima_reunion' => $request->proxima_reunion,
            ];
        }

        // Actualizamos la base de datos
        $planilla->update([
            'datos_planilla' => $contenido,
            // Opcional: Actualizar el nombre por si cambió la fecha
            // 'nombre_planilla' => ... 
        ]);

        return redirect()->route('planillas.principal')
            ->with('success', 'Planilla actualizada correctamente.');
    }
}