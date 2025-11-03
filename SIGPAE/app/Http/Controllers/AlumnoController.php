<?php

namespace App\Http\Controllers;
use App\Services\Interfaces\AlumnoServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AlumnoController extends Controller
{
    protected $alumnoService;

    public function __construct(AlumnoServiceInterface $alumnoService)
    {
        $this->alumnoService = $alumnoService;
    }

    public function index(): JsonResponse
    {
        $alumnos = $this->alumnoService->getAllAlumnosWithPersona();
        return response()->json($alumnos);
    }

    public function show(int $id): JsonResponse
    {
        $alumno = $this->alumnoService->getAlumnoWithPersona($id);
        if (!$alumno) {
            return response()->json(['message' => 'Alumno no encontrado'], 404);
        }
        return response()->json($alumno);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            // Pasamos todo el payload al servicio; el service separará persona/alumno
            $payload = $request->all();

            $alumno = $this->alumnoService->createAlumno($payload);
            return response()->json($alumno, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function vista()
    {
        $alumnos = $this->alumnoService->listar(); // ya incluye relaciones
        $cursos = \App\Models\Aula::all()->map(fn($aula) => $aula->descripcion)->unique();

        return view('alumnos.principal', compact('alumnos', 'cursos'));
    }

    public function destroy(int $id): JsonResponse
    {
        $resultado = $this->alumnoService->desactivar($id);
        if ($resultado) {
            return response()->json(['message' => 'Alumno desactivado correctamente']);
        }
        return response()->json(['message' => 'No se pudo desactivar el alumno'], 404);
    }

    public function filtro(Request $request)
    {
        $query = Alumno::with('persona', 'aula');

        if ($request->filled('nombre')) {
            $query->whereHas('persona', fn($q) => $q->where('nombre', 'like', '%' . $request->nombre . '%'));
        }

        if ($request->filled('apellido')) {
            $query->whereHas('persona', fn($q) => $q->where('apellido', 'like', '%' . $request->apellido . '%'));
        }

        if ($request->filled('documento')) {
            $query->whereHas('persona', fn($q) => $q->where('dni', 'like', '%' . $request->documento . '%'));
        }

        if ($request->filled('aula') && str_contains($request->aula, '°')) {
            [$curso, $division] = explode('°', $request->aula);
            $query->whereHas('aula', fn($q) => $q
                ->where('curso', $curso)
                ->where('division', $division));
        }

        $alumnos = $query->get();
        $cursos = Aula::all()->map(fn($aula) => $aula->descripcion)->unique();
        
        return view('alumnos.index', compact('alumnos', 'cursos'));
    }



}