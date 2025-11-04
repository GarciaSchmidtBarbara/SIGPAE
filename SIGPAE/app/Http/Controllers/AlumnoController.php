<?php

namespace App\Http\Controllers;
use App\Services\Interfaces\AlumnoServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Alumno;
use App\Models\Aula;

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
            $alumno = $this->alumnoService->crearAlumno($request->all());
            return response()->json($alumno, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
            //return redirect()->route('alumnos.principal')->with('success', 'Alumno creado correctamente');

        }
    }

    public function destroy(int $id): JsonResponse
    {
        $resultado = $this->alumnoService->desactivar($id);
        if ($resultado) {
            return response()->json(['message' => 'Alumno desactivado correctamente']);
        }
        return response()->json(['message' => 'No se pudo desactivar el alumno'], 404);
    }

    public function vista(Request $request)
    {
        $query = Alumno::with('persona', 'aula');

        if ($request->filled('nombre')) {
            $nombre = strtolower($this->quitarTildes($request->nombre));
            $query->whereHas('persona', function ($q) use ($nombre) {
                $q->whereRaw("LOWER(unaccent(nombre::text)) LIKE ?", ["%{$nombre}%"]);
            });
        }

       if ($request->filled('apellido')) {
            $apellido = strtolower($this->quitarTildes($request->apellido));
            $query->whereHas('persona', function ($q) use ($apellido) {
                $q->whereRaw("LOWER(unaccent(apellido)) LIKE ?", ["%{$apellido}%"]);
            });
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
        
        return view('alumnos.principal', compact('alumnos', 'cursos'));
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
        return view('alumnos.crear-editar', compact('cursos'));
    }



}