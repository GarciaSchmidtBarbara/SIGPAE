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

    public function crear()
    {
        // Preparamos un array de 'familiar' vacío para el formulario
        $familiarData = [
            'id' => null, // Importante: id=null significa que es NUEVO
            'tipo' => '', // Asumiendo que 'tipo' es un campo
            'nombre' => '',
            'apellido' => '',
            'dni' => '',
            // ... agrega cualquier otro campo de familiar aquí con ''
        ];
        
        // Le pasamos el 'indice' como null para que el formulario sepa
        // que estamos en modo "Crear".
        return view('familiares.crear-editar', [
            'familiarData' => $familiarData,
            'indice' => null
        ]);
    }

    public function editar(int $indice)
    {
        // obtengo todos los familiares de la sesion
        $familiares = session('asistente.familiares', []);

        // verifico que existe este familiar que quiero editar, aunque si existe pero por las dudas
        if (!isset($familiares[$indice])) {
            // Sde no existir lo mando de vuelta al usuario a la vista de crear-aditar del alumno
            return redirect()->route('alumnos.crear')->with('error', 'Error: No se pudo encontrar el familiar para editar.');
        }

        // paso los datos de ese familiar a la vista
        $familiarData = $familiares[$indice];

        // tambien le paso el indice para que el formulario de l avista alumnos/crear-editar sepa qué familiar está editando.
        return view('familiares.crear-editar', ['familiarData' => $familiarData,'indice' => $indice]);
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

        //Agrego logica de si existe el indice, y si esta en el array, guardo los datos de familiar ahi mismo
        //sino lo agrego al final del array
        $editIndex = $request->input('edit_familiar_index');
        if (is_numeric($editIndex) && isset($familiares_temp[$editIndex])) {
            $familiares_temp[$editIndex] = $tempFamiliar;
            $message = 'Familiar actualizado en la lista temporal.';

        } else {
            $familiares_temp[] = $tempFamiliar;
            $message = 'Familiar agregado a la lista temporal.';
        }

        Session::put('familiares_temp', $familiares_temp);
        
        return redirect()->route('alumnos.crear-editar')->with('success', $message);
    
    }
}