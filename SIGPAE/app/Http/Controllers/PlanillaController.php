<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Planilla;

class PlanillaController extends Controller
{
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
}