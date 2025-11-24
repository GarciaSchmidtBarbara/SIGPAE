<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use App\Services\Interfaces\IntervencionServiceInterface;

use App\Models\Intervencion;
use App\Models\Alumno;
use App\Models\Profesional;
use App\Models\Aula;


class IntervencionController extends Controller
{
    public function __construct(IntervencionServiceInterface $intervencionService)
    {
        $this->service = $intervencionService;

    }

   public function vista(Request $request)
{
    $filters = [
        'tipo_intervencion' => $request->input('tipo_intervencion'),
        'nombre'            => $request->input('nombre'),
        'aula_id'           => $request->input('aula_id'),
        'fecha_desde'       => $request->input('fecha_desde'),
        'fecha_hasta'       => $request->input('fecha_hasta'),
    ];

    $intervencionesRaw = $this->service->obtenerIntervenciones($filters);
    $tiposIntervencion = $this->service->obtenerTipos(); 
    $cursos = $this->service->obtenerAulasParaFiltro();

    $intervenciones = $intervencionesRaw->map(function ($intervencion) {
        $alumnos = $intervencion->alumnos->map(function ($alumno) {
            $persona = $alumno->persona;
            return $persona ? ($persona->nombre . ' ' . $persona->apellido) : 'N/A';
        })->implode(', ');

        $profesional = $intervencion->profesionalGenerador?->persona;

        $profesionalesReune = $intervencion->profesionales->map(function ($profesional) {
            $persona = $profesional->persona;
            return $persona ? ($persona->nombre . ' ' . $persona->apellido) : 'N/A';
        });

        $otrosProfesionales = $intervencion->otros_asistentes_i->map(function ($asistente) {
            $profesional = $asistente->profesional;
            $persona = $profesional?->persona;
            return $persona ? ($persona->nombre . ' ' . $persona->apellido) : 'N/A';
        });

        $todosProfesionales = $profesionalesReune
            ->merge($otrosProfesionales)
            ->merge($profesional ? collect([$profesional->nombre . ' ' . $profesional->apellido]) : collect())
            ->unique()
            ->implode(', ');

        return [
            'id_intervencion' => $intervencion->id_intervencion,
            'fecha_hora_intervencion' => $intervencion->fecha_hora_intervencion
                ? Carbon::parse($intervencion->fecha_hora_intervencion)->format('d/m/Y H:i')
                : 'Sin fecha',
            'tipo_intervencion' => $intervencion->tipo_intervencion,
            'alumnos' => $alumnos ?: 'Sin alumnos',
            'profesionales' => $todosProfesionales ?: 'Sin participantes',
            'activo' => $intervencion->activo,
        ];
    });

    return view('intervenciones.principal', compact('intervenciones', 'tiposIntervencion', 'cursos'));
}


    public function crear()
    {
        $alumnos = Alumno::with('persona', 'aula')->get();
        $profesionales = Profesional::with('persona')->get();
        $aulas = Aula::all();

        return view('intervenciones.crear-editar', [
            'modo' => 'crear',
            'alumnos' => $alumnos,
            'profesionales' => $profesionales,
            'aulas' => $aulas,
        ]);
    }

    public function guardar(Request $request)
    {
        $data = $request->validate([
            'fecha_hora_intervencion' => 'required|date',
            'lugar' => 'required|string|max:255',
            'modalidad' => 'required|string',
            'otra_modalidad' => 'nullable|string',
            'temas_tratados' => 'nullable|string',
            'compromisos' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        try {
            $intervencion = $this->service->crear($data);
            return redirect()
                ->route('intervenciones.principal')
                ->with('success', 'Intervención creada exitosamente.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function iniciarEdicion(int $id): View
    {
        $intervencion = $this->service->buscar($id);

        if (!$intervencion) {
            return redirect()
                ->route('intervenciones.principal')
                ->with('error', 'Intervención no encontrada.');
        }

        // === Alumnos seleccionados con datos completos para Alpine ===
        $alumnosSeleccionados = $intervencion->alumnos->map(function ($al) {
            $persona = $al->persona;
            return [
                'id' => $al->id_alumno,
                'nombre' => $persona->nombre,
                'apellido' => $persona->apellido,
                'dni' => $persona->dni,
                'fecha_nacimiento' => $persona->fecha_nacimiento
                    ? Carbon::parse($persona->fecha_nacimiento)->format('d/m/Y')
                    : 'N/A',
                'nacionalidad' => $persona->nacionalidad ?? 'N/A',
                'domicilio' => $persona->domicilio ?? 'N/A',
                'edad' => $persona->fecha_nacimiento
                    ? Carbon::parse($persona->fecha_nacimiento)->age
                    : 'N/A',
                'curso'   => $al->aula?->descripcion,
                'aula_id' => $al->fk_id_aula,
            ];
        })->toArray();

        // Profesionales participantes
        $profesionalesSeleccionados = $intervencion->profesionales->map(function ($prof) {
            $persona = $prof->persona;
            return [
                'id' => $prof->id_profesional,
                'nombre' => $persona->nombre ?? null,
                'apellido' => $persona->apellido ?? null,
                'profesion' => $prof->profesion ?? 'N/A',
            ];
        })->toArray();

        // Aulas seleccionadas
        $aulasSeleccionadas = $intervencion->aulas->pluck('id_aula')->toArray();

        // Alumno individual (si aplica)
        $initialAlumnoId = null;
        $initialAlumnoInfo = null;
        if ($intervencion->tipo_intervencion === 'INDIVIDUAL') {
            $alumno = $intervencion->alumnos->first();
            if ($alumno) {
                $initialAlumnoId = $alumno->id_alumno;
                $initialAlumnoInfo = [
                    'id' => $alumno->id_alumno,
                    'nombre' => $alumno->persona->nombre,
                    'apellido' => $alumno->persona->apellido,
                    'dni' => $alumno->persona->dni,
                    'curso' => $alumno->aula?->descripcion,
                    'aula_id' => $alumno->fk_id_aula,
                ];
            }
        }

        // Mapping completo de alumnos para Alpine
        $alumnosJson = $intervencion->alumnos->mapWithKeys(function ($al) {
            $persona = $al->persona;
            return [
                $al->id_alumno => [
                    'id' => $al->id_alumno,
                    'nombre' => $persona->nombre,
                    'apellido' => $persona->apellido,
                    'dni' => $persona->dni,
                    'curso' => $al->aula?->descripcion,
                    'aula_id' => $al->fk_id_aula,
                ]
            ];
        });

        // Colecciones completas para selects
        $alumnos = Alumno::with('persona', 'aula')->get();
        $profesionales = Profesional::with('persona')->get();
        $aulas = Aula::all();

        return view('intervenciones.crear-editar', [
            'modo' => 'editar',
            'intervencion' => $intervencion,
            'alumnosSeleccionados' => $alumnosSeleccionados,
            'profesionalesSeleccionados' => $profesionalesSeleccionados,
            'aulasSeleccionadas' => $aulasSeleccionadas,
            'alumnos' => $alumnos,
            'aulas' => $aulas,
            'profesionales' => $profesionales,
            'alumnosJson' => $alumnosJson,
            'initialAlumnoId' => $initialAlumnoId,
            'initialAlumnoInfo' => $initialAlumnoInfo,
        ]);
    }

    public function editar(int $id)
    {
        $intervencion = $this->service->buscar($id);

        if (!$intervencion) {
            return redirect()->route('intervenciones.principal')
                            ->with('error', 'Intervención no encontrada.');
        }

        $alumnos = Alumno::with('persona', 'aula')->get();
        $profesionales = Profesional::with('persona')->get();
        $aulas = Aula::all();

        return view('intervenciones.crear-editar', [
            'modo' => 'editar',
            'intervencion' => $intervencion,
            'alumnos' => $alumnos,
            'profesionales' => $profesionales,
            'aulas' => $aulas,
        ]);
    }

    public function actualizar(Request $request, int $id)
    {
        $data = $request->validate([
            'fecha_hora_intervencion' => 'required|date',
            'lugar' => 'required|string|max:255',
            'modalidad' => 'required|string',
            'otra_modalidad' => 'nullable|string',
            'temas_tratados' => 'nullable|string',
            'compromisos' => 'nullable|string',
            'observaciones' => 'nullable|string',
        ]);

        try {
            $this->service->actualizar($id, $data);
            return redirect()
                ->route('intervenciones.principal')
                ->with('success', 'Intervención actualizada.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function eliminar(int $id)
    {
        $ok = $this->service->eliminar($id);

        return redirect()->route('intervenciones.principal')
                        ->with($ok ? 'success' : 'error', $ok ? 'Intervención eliminada.' : 'No se pudo eliminar.');
    }

    public function cambiarActivo(int $id)
    {
        $ok = $this->service->cambiarActivo($id);

        return redirect()->route('intervenciones.principal')
                        ->with($ok ? 'success' : 'error', $ok ? 'Intervención actualizada.' : 'No se pudo actualizar.');
    }

}
