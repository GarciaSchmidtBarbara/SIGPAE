<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use App\Services\Interfaces\FamiliarServiceInterface;


class FamiliarController extends Controller
{
    protected $familiarService;

    public function __construct(FamiliarServiceInterface $familiarService)
    {
        $this->familiarService = $familiarService;
    }

    public function index(): JsonResponse
    {
        $familiares = $this->familiarService->getAllFamiliaresWithPersona();
        return response()->json($familiares);
    }

    public function show(int $id): JsonResponse
    {
        $familiar = $this->familiarService->getFamiliarWithPersona($id); 
        if (!$familiar) {
            return response()->json(['message' => 'Familiar no encontrado'], 404);
        }
        return response()->json($familiar);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $payload = $request->all();

            $familiar = $this->familiarService->createFamiliar($payload);
            return response()->json($familiar, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $data = $request->all();
            $familiar = $this->familiarService->updateFamiliar($id, $data);
            return response()->json($familiar);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        if ($this->familiarService->deleteFamiliar($id)) {
            return response()->json(null, 204);
        }
        return response()->json(['message' => 'Familiar no encontrado'], 404);
    }

    public function create()
    {
        return view('familiares.crear-familiar');
    }

    public function storeAndReturn(Request $request): RedirectResponse
    {
        $tempFamiliar = [
            // Persona (si no hay fk_id_persona, o sea, no es un hermano ya creado en el sistema)
            'nombre'            => $request->string('nombre')->toString(),
            'apellido'          => $request->string('apellido')->toString(),
            'dni'               => $request->string('documento')->toString(),
            'fecha_nacimiento'  => $request->string('fecha_nacimiento')->toString(),
            'domicilio'         => $request->string('domicilio')->toString(),
            'nacionalidad'      => $request->string('nacionalidad')->toString(),

            // Familiar
            'telefono_personal' => $request->string('telefono_personal')->toString(),
            'telefono_laboral'  => $request->string('telefono_laboral')->toString(),
            'lugar_de_trabajo'      => $request->string('lugar_de_trabajo')->toString(),
            'parentesco'        => $request->string('parentesco')->toString(), // valores: padre,madre,hermano,tutor,otro
            'otro_parentesco'   => $request->string('otro_parentesco')->toString(),
            'observaciones'     => $request->string('observaciones')->toString(),

            // Si es hermano seleccionado desde buscador
            'fk_id_persona'     => $request->input('fk_id_persona'),
            'asiste_a_institucion' => $request->boolean('asiste_a_institucion'),
        ];
        
        
        $familiares_temp = Session::get('familiares_temp', []);
        $familiares_temp[] = $tempFamiliar;
        Session::put('familiares_temp', $familiares_temp);
        
        return redirect()->route('alumnos.crear-editar')->with('success', 'Familiar agregado a la lista temporal.');
    }

    public function removeTempFamiliar(int $index): JsonResponse
    {
        $familiares_temp = Session::get('familiares_temp', []);
        
        if (isset($familiares_temp[$index])) {
            array_splice($familiares_temp, $index, 1);
            Session::put('familiares_temp', $familiares_temp);
            return response()->json(['success' => true]);
        }
        
        return response()->json(['success' => false, 'message' => 'Familiar no encontrado'], 404);
    }
}