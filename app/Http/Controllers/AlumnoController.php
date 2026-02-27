<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use App\Services\Interfaces\AlumnoServiceInterface;
use App\Services\Interfaces\PersonaServiceInterface;
use App\Services\Interfaces\AulaServiceInterface;
use App\Services\Interfaces\DocumentoServiceInterface;

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
    protected AlumnoServiceInterface $alumnoService;
    protected PersonaServiceInterface $personaService;
    protected AulaServiceInterface $aulaService;
    protected DocumentoServiceInterface $documentoService;

    public function __construct(
        AlumnoServiceInterface $alumnoService,
        AulaServiceInterface $aulaService,
        PersonaServiceInterface $personaService,
        DocumentoServiceInterface $documentoService
    ) {
        $this->alumnoService    = $alumnoService;
        $this->personaService   = $personaService;
        $this->aulaService      = $aulaService;
        $this->documentoService = $documentoService;
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
        $cursos = $this->aulaService->obtenerCursos();

        return view('alumnos.principal', compact('alumnos', 'cursos'));
    }

    public function buscar(Request $request): JsonResponse
    {
        $q = (string)$request->get('q', '');

        // Verifico si existe el parámetro, y si existe lo forzamos a ser un Número Entero
        $excludeId = $request->filled('exclude_id') ? (int) $request->get('exclude_id') : null;

        return response()->json($this->alumnoService->buscar($q, $excludeId));
    }

    public function crear() {
        // limpio la sesion manualmente antes de entrar, en caso de que el usuario abra otra pestaña en el navegador
        // en caso de ir a otra ruta que no pertenece a la cobertura del middleware, este ultimo es quien se encarga de limpiar la sesion
        session()->forget('asistente');

        $cursos = $this->aulaService->obtenerCursos();
        
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

        // Limpiar sesión del asistente antes de cargar nuevos datos
        session()->forget('asistente');

        $cursos = $this->aulaService->obtenerCursos();

        // Toda la transformación de datos es responsabilidad del service
        $datos = $this->alumnoService->prepararDatosEdicion($alumno);

        session([
            'asistente.alumno'                      => $datos['alumnoData'],
            'asistente.familiares'                  => $datos['familiares'],
            'asistente.familiares_a_eliminar'       => [],
            'asistente.hermanos_alumnos_a_eliminar' => [],
        ]);

        // Documentos del alumno para la sección de documentación
        $documentos = $this->documentoService->listarParaAlumno($alumno->id_alumno);

        return view('alumnos.crear-editar', compact('cursos', 'alumno', 'documentos'))->with('modo', 'editar');
    }

    /**
     * Muestra la vista del alumno recuperando el estado
     * de la sesión actual, sin limpiar nada.
     * Se usa al volver de la carga de familiares.
     */
    public function continuar()
    {
        // 1. Obtenemos dependencias básicas para la vista (selects)
        $cursos = $this->aulaService->obtenerCursos();

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
        $dniIngresado = (string) $request->input('dni');
        $idAlumnoActual = $request->input('id_alumno');

        $familiares = session('asistente.familiares', []);
        foreach ($familiares as $familiar) {
            if (isset($familiar['dni']) && $familiar['dni'] === $dniIngresado) {
                return response()->json(['valid' => false, 'message' => 'Este DNI ya fue asignado a un familiar en esta carga.']);
            }
        }

        $personaEnBBDD = $this->personaService->findPersonaByDni($dniIngresado);

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
        //dd(session()->all());
        
        //dd($request->all());
        
        // 1. Validación del Alumno
        // A diferencia de la vista, aquí sí validamos contra la BBDD que el DNI sea único.
        $datosAlumno = $request->validate([
            'dni' => 'required|string|unique:personas,dni',
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

    // ── Documentos del alumno ──────────────────────────────────────────────────

    public function subirDocumento(Request $request, int $id): JsonResponse
    {
        $alumno = $this->alumnoService->obtener($id);
        if (!$alumno) {
            return response()->json(['error' => 'Alumno no encontrado.'], 404);
        }

        $request->validate([
            'nombre'  => 'required|string|max:191',
            'archivo' => 'required|file|max:10240',
        ]);

        try {
            $idProfesional = auth()->id();
            $doc = $this->documentoService->subir(
                [
                    'nombre'                => $request->input('nombre'),
                    'contexto'              => 'perfil_alumno',
                    'fk_id_entidad'         => $id,
                    'disponible_presencial' => false,
                ],
                $request->file('archivo'),
                $idProfesional
            );

            return response()->json([
                'id_documento'  => $doc->id_documento,
                'nombre'        => $doc->nombre,
                'tipo_formato'  => $doc->tipo_formato?->value ?? '',
                'tamanio'       => $doc->tamanio_formateado,
                'fecha'         => $doc->fecha_hora_carga?->format('d/m/Y'),
                'ruta_descarga' => route('documentos.descargar', $doc->id_documento),
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function eliminarDocumento(int $id, int $docId): JsonResponse
    {
        try {
            $this->documentoService->eliminar($docId);
            return response()->json(null, 204);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────

    public function actualizar(Request $request, int $id)
    {
        //dd(session()->all());

        $alumno = $this->alumnoService->obtener($id);

        // Validación manual porque obtener() devuelve null si no encuentra, no lanza excepción automática
        if (!$alumno) {
            return redirect()->route('alumnos.principal')->with('error', 'Alumno no encontrado.');
        }

        $idPersona = $alumno->persona->id_persona;

        // 1. Validación del Alumno
        $datosAlumno = $request->validate([
            'dni' => 'required|string|unique:personas,dni,'. $idPersona . ',id_persona',
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

            // Documentos a eliminar (marcados en la UI)
            'docs_a_eliminar'   => 'nullable|array',
            'docs_a_eliminar.*' => 'integer',
        ]);

        try {
            $familiares = session('asistente.familiares', []);
            $familiares_a_eliminar = session('asistente.familiares_a_eliminar', []);
            $hermanos_alumnos_a_eliminar = session('asistente.hermanos_alumnos_a_eliminar', []);

            // 3. Delegamos al Servicio la lógica pesada
            // (El servicio orquestará la transacción de BBDD)
            $this->alumnoService->actualizar(
                $id, 
                $datosAlumno, 
                $familiares, 
                $familiares_a_eliminar, 
                $hermanos_alumnos_a_eliminar
            );

            // Eliminar documentos marcados desde la UI (delegado al service)
            $this->documentoService->eliminarVarios(
                $request->input('docs_a_eliminar', [])
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