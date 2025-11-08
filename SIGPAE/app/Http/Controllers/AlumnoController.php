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
            $familiaresTemp = Session::get('familiares_temp', []);
            $alumno = $this->alumnoService->crearAlumnoConFamiliares($request->all(), $familiaresTemp);
            Session::forget('familiares_temp');
            return response()->json($alumno, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
            //return redirect()->route('alumnos.principal')->with('success', 'Alumno creado correctamente');

        }
    }

    public function cambiarActivo(int $id): RedirectResponse
    {
        $resultado = $this->alumnoService->cambiarActivo($id);
        if ($resultado) {
            return redirect()
                ->route('alumnos.principal')
                ->with('success', 'El estado del alumno fue actualizado correctamente.');
        }
        return redirect()
            ->route('alumnos.principal')
            ->with('error', 'No pudo realizarse la actualizacion de estado del alumno.');
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



}