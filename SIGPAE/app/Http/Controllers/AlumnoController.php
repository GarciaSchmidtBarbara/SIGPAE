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
        session()->forget('asistente');

        $cursos = $this->alumnoService->obtenerCursos();
        
        // Preparamos la sesión con la estructura vacía del asistente
        session([
            'asistente.alumno' => [
                // Rellenamos los campos vacíos para evitar errores en la vista
                'id_alumno' => null, 'dni' => '', 'nombre' => '', 'apellido' => '', 'fecha_nacimiento' => '',
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

        $coleccionUnificada = $familiares_puros->merge($hermanos_alumnos);

        // 3. [NUEVO] Normalización / Aplanado de Datos
        // Transformamos la estructura anidada de BBDD a la estructura plana que espera la Vista
        $familiares_array = $coleccionUnificada->map(function ($item) {
            $data = $item->toArray();

            // A. Aplanar datos de PERSONA (nombre, apellido, dni...)
            if (isset($data['persona'])) {
                $data['nombre'] = $data['persona']['nombre'] ?? '';
                $data['apellido'] = $data['persona']['apellido'] ?? '';
                $data['dni'] = $data['persona']['dni'] ?? '';
                
                $data['fecha_nacimiento'] = $item->persona->fecha_nacimiento ?? '';
                    
                $data['domicilio'] = $data['persona']['domicilio'] ?? '';
                $data['nacionalidad'] = $data['persona']['nacionalidad'] ?? '';
                
                // Importante para la lógica de Hermano Alumno
                $data['fk_id_persona'] = $data['persona']['id_persona'] ?? null;
            }

            // B. Aplanar datos de AULA (Para Hermanos)
            if (isset($data['aula'])) {
                $data['curso'] = $data['aula']['curso'] ?? '';
                $data['division'] = $data['aula']['division'] ?? '';
            }

            // C. Aplanar datos PIVOTE (Observaciones y Activa)
            // Eloquent pone los datos de la tabla intermedia en 'pivot'
            if (isset($data['pivot'])) {
                $data['observaciones'] = $data['pivot']['observaciones'] ?? '';
                // Nota: id_familiar o id_alumno ya vienen en el array base
            }
            
            // D. Corrección de Parentesco para Hermanos BBDD
            if (!isset($data['parentesco'])) {
                // Si no tiene parentesco, es un Hermano Alumno
                $data['parentesco'] = null; // La marca de "hermano alumno de BBDD"
                $data['asiste_a_institucion'] = 1;
            } else {
                // Si tiene parentesco, lo pasamos a minúscula para el radio button
                $data['parentesco'] = strtolower($data['parentesco']);
            }

            return $data;
        })->toArray();

        // 4. Guardamos en sesión
        session([
            'asistente.alumno' => $alumnoData,
            'asistente.familiares' => $familiares_array, // Array limpio y plano
            'asistente.familiares_a_eliminar' => [],
            'asistente.hermanos_alumnos_a_eliminar' => []
        ]);

        return view('alumnos.crear-editar', compact('cursos', 'alumno'))->with('modo', 'editar');
    }

    /**
     * Muestra la vista del alumno recuperando el estado 
     * de la sesión actual, sin limpiar nada.
     * Se usa al volver de la carga de familiares.
     */
    public function continuar()
    {
        // 1. Obtenemos dependencias básicas para la vista (selects)
        // (Idealmente usarías tu servicio, aquí replico lo que tienes en crear/editar)
        $cursos = $this->alumnoService->obtenerCursos();

        // leo los datos del alumno de la sesión para saber en qué modo estamos
        $alumnoData = session('asistente.alumno', []);

        $idAlumno = $alumnoData['id_alumno'] ?? null; 
        $modo = $idAlumno ? 'editar' : 'crear';

        // Si es edición, necesitamos el objeto Alumno para la lógica extra de la vista
        // (botones de estado, rutas que piden el objeto, etc.)
        $alumno = null;
        if ($modo === 'editar') {
            // Buscamos el alumno (sin cargar relaciones pesadas, solo lo básico para la vista)
            $alumno = $this->alumnoService->obtener($idAlumno);
        }

        // no paso 'alumnoData' ni 'familiares' explícitamente porque 
        // la vista ya sabe leerlos de session('asistente...') en su x-data.
        return view('alumnos.crear-editar', compact('cursos', 'alumno'))
            ->with('modo', $modo);
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

        if ($tipo === 'familiar') {
            // si es familiar puro, busco 'id_familiar'
            $id_a_borrar = $item_a_borrar['id_familiar'] ?? null;
        } elseif ($tipo === 'hermano') {
            // si es hermano alumno, busco 'id_alumno'
            $id_a_borrar = $item_a_borrar['id_alumno'] ?? null;
        }

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

    public function validarDniAjax(Request $request): JsonResponse
    {
        $dniIngresado = $request->input('dni');
        $idAlumnoActual = $request->input('id_alumno');

        $familiares = session('asistente.familiares', []);
        foreach ($familiares as $familiar) {
            if (isset($familiar['dni']) && $familiar['dni'] === $dniIngresado) {
                return response()->json(['valid' => false, 'message' => 'Este DNI ya fue asignado a un familiar en esta carga.']);
            }
        }

        $personaEnBBDD = \App\Models\Persona::where('dni', $dniIngresado)->first();

        if ($personaEnBBDD) {
            $alumnoAsociado = $personaEnBBDD->alumno; 

            if ($idAlumnoActual && $alumnoAsociado && $idAlumnoActual == $alumnoAsociado->id_alumno) {
                return response()->json(['valid' => true]);
            }
            
            return response()->json(['valid' => false, 'message' => 'DNI ya registrado en el sistema.']);
        }

        return response()->json(['valid' => true]);
    }

    public function guardar(Request $request)
    {
        // 1. Validación del Alumno
        // A diferencia de la vista, aquí sí validamos contra la BBDD que el DNI sea único.
        $datosAlumno = $request->validate([
            'dni' => 'required|numeric',
            'nombre' => 'required|string|max:191',
            'apellido' => 'required|string|max:191',
            'fecha_nacimiento' => 'required|date',
            'nacionalidad' => 'nullable|string|max:191',
            'aula' => 'required', 
            'inasistencias' => 'nullable|integer',
            'cud' => 'required|string',
            // Campos de situación y observaciones
            'situacion_socioeconomica' => 'nullable|string',
            'situacion_familiar' => 'nullable|string',
            'situacion_medica' => 'nullable|string',
            'situacion_escolar' => 'nullable|string',
            'actividades_extraescolares' => 'nullable|string',
            'intervenciones_externas' => 'nullable|string',
            'antecedentes' => 'nullable|string',
            'observaciones' => 'nullable|string'
        ]);

        try {
            $familiares = session('asistente.familiares', []);
            $this->alumnoService->crearAlumnoConFamiliares($datosAlumno, $familiares);
            session()->forget('asistente');
            
            return redirect()->route('alumnos.principal')
                             ->with('success', 'Alumno y familiares registrados correctamente.');
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    public function actualizar(Request $request, int $id)
    {
        // 1. Validación del Alumno
        $datosAlumno = $request->validate([
            'dni' => 'required|numeric', 
            'nombre' => 'required|string|max:191',
            'apellido' => 'required|string|max:191',
            'fecha_nacimiento' => 'required|date',
            'nacionalidad' => 'nullable|string|max:191',
            'aula' => 'required|string',
            'inasistencias' => 'nullable|integer',
            'cud' => 'required|string',
            
            // Campos opcionales
            'situacion_socioeconomica' => 'nullable|string',
            'situacion_familiar' => 'nullable|string',
            'situacion_medica' => 'nullable|string',
            'situacion_escolar' => 'nullable|string',
            'actividades_extraescolares' => 'nullable|string',
            'intervenciones_externas' => 'nullable|string',
            'antecedentes' => 'nullable|string',
            'observaciones' => 'nullable|string',
        ]);

        try {
            $familiares = session('asistente.familiares', []);
            $familiares_a_eliminar = session('asistente.familiares_a_eliminar', []);
            $hermanos_a_eliminar = session('asistente.hermanos_alumnos_a_eliminar', []);

            // 3. Delegamos al Servicio la lógica pesada
            // (El servicio orquestará la transacción de BBDD)
            $this->alumnoService->actualizarAlumno(
                $id, 
                $datosAlumno, 
                $familiares, 
                $familiares_a_eliminar, 
                $hermanos_a_eliminar
            );

            // 4. Limpieza y Éxito
            session()->forget('asistente');

            return redirect()->route('alumnos.principal')
                             ->with('success', 'Alumno actualizado correctamente.');

        } catch (\Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

}