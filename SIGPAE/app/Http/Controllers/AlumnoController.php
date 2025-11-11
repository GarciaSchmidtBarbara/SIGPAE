<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use App\Services\Interfaces\AlumnoServiceInterface;


use App\Models\Alumno;
use App\Models\Aula;

//capa de presentación (interactúa con la vista).
//Su única responsabilidad es:
    //Recibir la solicitud HTTP (GET, POST…)
    //Pasar los parámetros al servicio
    //Retornar una vista o un redirect
//NO debe contener lógica de negocio ni consultas.

class AlumnoController extends Controller
{
    public function __construct(AlumnoServiceInterface $alumnoService)
    {
        $this->alumnoService = $alumnoService;
    }

    public function index(): JsonResponse
    {
        $alumnos = $this->alumnoService->listar();
        return response()->json($alumnos);
    }

    public function show(int $id): JsonResponse
    {
        $alumno = $this->alumnoService->obtener($id);
        if (!$alumno) {
            return response()->json(['message' => 'Alumno no encontrado'], 404);
        }
        return response()->json($alumno);
    }

    public function store(Request $request)
    {
        try {
            $familiaresTemp = Session::get('familiares_temp', []);
            $alumno = $this->alumnoService->crearAlumnoConFamiliares($request->all(), $familiaresTemp);
            
            // Limpiar las sesiones temporales
            Session::forget('familiares_temp');
            Session::forget('alumno_temp');
            
            //Si es una petición AJAX, retornar JSON
            if ($request->expectsJson()) {
                return response()->json($alumno, 201);
            }
            
            //Si es una petición normal del formulario, redirigir
            return redirect()->route('alumnos.principal')->with('success', 'Alumno creado correctamente');
            
        } catch (\Exception $e) {
            //Si es una petición AJAX, retornar JSON error
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 400);
            }
            
            //Si es una petición normal, redirigir con error
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear el alumno: ' . $e->getMessage());
        }
    }

    public function cambiarActivo(int $id): RedirectResponse
    {
        $resultado = $this->alumnoService->cambiarActivo($id);
        $mensaje = $resultado
            ? ['success' => 'El estado del alumno fue actualizado correctamente.']
            : ['error' => 'No pudo realizarse la actualización de estado del alumno.'];

        return redirect()->route('alumnos.principal')->with($mensaje);
    }

    public function vista(Request $request)
    {
        $alumnos = $this->alumnoService->filtrar($request);
        $cursos = $this->alumnoService->obtenerCursos();

        return view('alumnos.principal', compact('alumnos', 'cursos'));
    }

    public function buscar(Request $request): JsonResponse
    {
        $q = (string)$request->get('q', '');
        return response()->json($this->alumnoService->buscar($q));
    }
    private function quitarTildes(string $texto): string
    {
        return strtr(
            iconv('UTF-8', 'ASCII//TRANSLIT', $texto),
            "´`^~¨",
            "     "
        );
    }

   public function crearEditar() {
        $cursos = Aula::all()->map(fn($aula) => $aula->descripcion)->unique();
        $familiares_temp = Session::get('familiares_temp', []);
        $alumnoData = Session::get('alumno_temp', []);
        
        return view('alumnos.crear-editar', compact('cursos', 'familiares_temp', 'alumnoData'));
    }

    //Para mantener los datos del alumno al ir a crear un familiar
    public function prepareFamiliarCreation(Request $request): RedirectResponse
    {
        // Store all form data except token in session
        $alumnoData = $request->except(['_token']);
        Session::put('alumno_temp', $alumnoData);

        return redirect()->route('familiares.create');
    }

    public function editar(int $id)
    {
        $alumno = $this->alumnoService->obtener($id);
        if (!$alumno) {
            return redirect()->route('alumnos.principal')->with('error', 'Alumno no encontrado.');
        }

        $cursos = $this->alumnoService->obtenerCursos();

        // Convertir datos del modelo en un array simple para la vista
        $alumnoData = [
            'dni' => $alumno->persona->dni,
            'nombre' => $alumno->persona->nombre,
            'apellido' => $alumno->persona->apellido,
            'fecha_nacimiento' => $alumno->persona->fecha_nacimiento,
            'nacionalidad' => $alumno->persona->nacionalidad,
            'aula' => $alumno->aula->descripcion,
            'inasistencias' => $alumno->inasistencias,
            'cud' => $alumno->cud ? 'Sí' : 'No',
            'situacion_socioeconomica' => $alumno->situacion_socioeconomica,
            'situacion_familiar' => $alumno->situacion_familiar,
            'situacion_medica' => $alumno->situacion_medica,
            'situacion_escolar' => $alumno->situacion_escolar,
            'actividades_extraescolares' => $alumno->actividades_extraescolares,
            'intervenciones_externas' => $alumno->intervenciones_externas,
            'antecedentes' => $alumno->antecedentes,
            'observaciones' => $alumno->observaciones,
        ];

        return view('alumnos.crear-editar', compact('alumnoData', 'cursos', 'alumno'))
            ->with('modo', 'editar');
    }

    public function actualizar(Request $request, int $id): RedirectResponse
    {
        try {
            $this->alumnoService->actualizar($id, $request->all());
            return redirect()->route('alumnos.principal')->with('success', 'Alumno actualizado correctamente.');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar el alumno: ' . $e->getMessage());
        }
    }


}