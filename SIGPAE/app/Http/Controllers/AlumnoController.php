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
        request->validate([
            'dni' => 'required|numeric',
            'nombre' => 'required|string|max:191',
            'apellido' => 'required|string|max:191',
            'fecha_nacimiento' => 'required|date',
            'nacionalidad' => 'required|string|max:191',
            'aula' => 'required|string',
        ], [
            'required' => 'Este campo es obligatorio.',
            'date' => 'Debe ingresar una fecha válida.',
            'numeric' => 'Debe ingresar un número válido.',
        ]);
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

    public function crear() {
        // limpio la sesion manualmente antes de entrar, en caso de que el usuario abra otra pestaña en el navegador
        // en caso de ir a otra ruta que no pertenece a la cobertura del middleware, este ultimo es quien se encarga de limpiar la sesion
        //session()->forget('asistente');

        $cursos = Aula::all()->map(fn($aula) => $aula->descripcion)->unique();
        
        // Preparamos la sesión con la estructura vacía del asistente
        session([
            'asistente.alumno' => [
                // Rellenamos los campos vacíos para evitar errores en la vista
                'dni' => '', 'nombre' => '', 'apellido' => '', 'fecha_nacimiento' => '',
                'nacionalidad' => '', 'aula' => '', 'inasistencias' => 0, 'cud' => 'No',
                'situacion_socioeconomica' => '', 'situacion_familiar' => '', 'situacion_medica' => '',
                'situacion_escolar' => '', 'actividades_extraescolares' => '', 'intervenciones_externas' => '',
                'antecedentes' => '', 'observaciones' => ''
            ],
            'asistente.familiares' => [],
            'asistente.familiares_a_eliminar' => [],
            'asistente.hermanos_alumnos_a_eliminar' => []
        ]);
                
        return view('alumnos.crear-editar', compact('cursos'))->with('modo', 'crear');
    }

    public function editar(int $id)
    {
        $alumno = $this->alumnoService->obtenerParaEditar($id);
        if (!$alumno) {
            return redirect()->route('alumnos.principal')->with('error', 'Alumno no encontrado.');
        }

        // limpio la sesion manualmente antes de entrar, en caso de que el usuario abra otra pestaña en el navegador
        // en caso de ir a otra ruta que no pertenece a la cobertura del middleware, este ultimo es quien se encarga de limpiar la sesion
        session()->forget('asistente');

        $cursos = $this->alumnoService->obtenerCursos();

        // Convertir datos del modelo en un array simple para la vista
        $alumnoData = [
            'id_alumno' => $alumno->id_alumno,
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

        //unificao los familiares puros con los hermanos alumnos
        $familiares_puros = $alumno->familiares;

        $hermanos_que_el_apunta = $alumno->hermanos;
        $hermanos_que_lo_apuntan = $alumno->esHermanoDe;
        $hermanos_alumnos = $hermanos_que_el_apunta->merge($hermanos_que_lo_apuntan);

        $familiares_unificados = $familiares_puros->merge($hermanos_alumnos)->toArray();

        // Poblamos la sesión con los datos del alumno y sus familiares
        session([
            'asistente.alumno' => $alumnoData,
            'asistente.familiares' => $familiares_unificados,
            'asistente.familiares_a_eliminar' => [],
            'asistente.hermanos_alumnos_a_eliminar' => []
        ]);

        return view('alumnos.crear-editar', compact('cursos', 'alumno'))->with('modo', 'editar');
    }

    public function eliminarItemDeSesion(Request $request, int $indice): JsonResponse
    {
        // cuando me refiero a item me refiero a un familiar o a un hermano alumno
        // btenemos el 'tipo' que pase por la url
        $tipo = $request->query('tipo'); 

        $familiares = session('asistente.familiares', []);

        if (!isset($familiares[$indice])) {
            return response()->json(['error' => 'Índice no válido'], 404);
        }

        $item_a_borrar = $familiares[$indice];
        $id_a_borrar = $item_a_borrar['id'] ?? null;

        if ($id_a_borrar) {
            if ($tipo === 'familiar') {
                // si es un familiar puro, lo preparo para el borrado logico
                session()->push('asistente.familiares_a_eliminar', $id_a_borrar);
            } else if ($tipo === 'hermano') {
                // si es un familiar "hermano alumno" lo preparo para el borrado fisico
                session()->push('asistente.hermanos_alumnos_a_eliminar', $id_a_borrar);
            }
        }

        // para todos los casos borro el item de la tabla de familiares
        array_splice($familiares, $indice, 1);
        session(['asistente.familiares' => $familiares]);

        return response()->json(null, 204);
    }

    //sincronizo el estado del formulario del asistente (en alpine) con la sesión de laravel
    public function sincronizarEstado(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'alumno' => 'required|array',
            'familiares' => 'present|array',
        ]);

        $familiares_eliminados = session('asistente.familiares_a_eliminar', []);
        $hermanos_alumnos_eliminados = session('asistente.hermanos_alumnos_a_eliminar', []);

        // sobreescribo los datos de la sesion con lo de alpine
        session([
            'asistente.alumno' => $datos['alumno'],
            'asistente.familiares' => $datos['familiares'],
            'asistente.familiares_a_eliminar' => $familiares_eliminados,
            'asistente.hermanos_alumnos_a_eliminar' => $hermanos_alumnos_eliminados
        ]);

        // devuelvo una repuesta vacia para que alpine sepa que salió bien
        return response()->json(null, 204);
    }

    public function actualizar(Request $request, int $id): RedirectResponse
    {
        try {
            $this->alumnoService->actualizar($id, $request->all());
            return redirect()->route('alumnos.principal')->with('success', 'Alumno actualizado correctamente.');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }


}