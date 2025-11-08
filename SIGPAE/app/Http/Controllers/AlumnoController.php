<?php

namespace App\Http\Controllers;
use App\Services\Interfaces\AlumnoServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

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

    public function crearEditar()
    {
        $cursos = $this->alumnoService->obtenerCursos();
        return view('alumnos.crear-editar', compact('cursos'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'dni' => 'required|numeric|unique:personas,dni',
                'nombre' => 'required|string|max:255',
                'apellido' => 'required|string|max:255',
                'fecha_nacimiento' => 'required|date_format:d/m/Y',
                'nacionalidad' => 'required|string|max:255',
                'aula' => 'required|string',
                'cud' => 'required|string|in:Sí,No',
            ]);

            $this->alumnoService->crearAlumno($validated);

            return redirect()
                ->route('alumnos.principal')
                ->with('success', 'El alumno fue creado correctamente.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

}