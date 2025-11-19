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
        // preparamos un array de 'familiar' vacío para el formulario
        $familiarData = [
            'id_familiar' => null,
            'fk_id_persona' => null,
            
            // datos de persona
            'nombre' => '',
            'apellido' => '',
            'documento' => '',
            'fecha_nacimiento' => '',
            'domicilio' => '',
            'nacionalidad' => '',

            // datos de familiar
            'telefono_personal' => '',
            'telefono_laboral' => '',
            'lugar_de_trabajo' => '',
            'parentesco' => '',
            'otro_parentesco' => '',
            'observaciones' => '',
            
            // campos para el hermano alumno
            'asiste_a_institucion' => false,
            'fk_id_persona' => null,
        ];
        
        // le paso indice == null porque es un familiar nuevo para la table de familiares
        return view('familiares.crear-editar', [
            'familiarData' => $familiarData,
            'indice' => null,
            'solo_lectura' => false // por defecto va a ser false en porque se crear familiar
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

        // Si tiene 'parentesco', es un Familiar Puro (Editable).
        // Si NO tiene 'parentesco', es un Hermano Alumno (Solo Lectura).
        $solo_lectura = !isset($familiarData['parentesco']);

        // tambien le paso el indice para que el formulario de l avista alumnos/crear-editar sepa qué familiar está editando.
        return view('familiares.crear-editar', ['familiarData' => $familiarData,'indice' => $indice, 'solo_lectura' => $solo_lectura]);
    }

    public function guardar(Request $request)
    {
        // Si viene "on", lo convertimos a 1. Si no viene, queda en 0.
        $request->merge([
            'asiste_a_institucion' => $request->has('asiste_a_institucion') ? 1 : 0,
        ]);
        // 1. Validamos los campos (usando la lista que descubrimos)
        // Nota: Ajustá las reglas según tus necesidades reales
        $datosFamiliar = $request->validate([
            'nombre' => 'required|string|max:191',
            'apellido' => 'required|string|max:191',
            'dni' => 'required|string|max:20',
            'fecha_nacimiento' => 'required|date',
            'domicilio' => 'nullable|string',
            'nacionalidad' => 'nullable|string',
            'telefono_personal' => 'nullable|string',
            'telefono_laboral' => 'nullable|string',
            'lugar_de_trabajo' => 'nullable|string',
            'parentesco' => 'required|string',
            'otro_parentesco' => 'nullable|string',
            'observaciones' => 'nullable|string',
            // Campos ocultos o de lógica
            'asiste_a_institucion' => 'boolean',
            'fk_id_persona' => 'nullable',
            'id_familiar' => 'nullable',        // ID del familiar (si existía)
        ]);

        $datosFamiliar['id_familiar'] = $request->input('id_familiar', null);
        $datosFamiliar['fk_id_persona'] = $request->input('fk_id_persona', null);

        // 2. Recuperar el array unificado de la sesión
        $familiares = session('asistente.familiares', []);

        // 3. Recuperar el índice (campo hidden del formulario)
        $indice = $request->input('indice');

        // 4. Guardamos o Actualizamos
        if (is_numeric($indice) && isset($familiares[$indice])) {
            // MODO EDITAR:
            // Usamos array_merge para conservar cualquier dato del array original 
            // que no este en el formulario, y sobreescribimos con los nuevos datos.
            $familiares[$indice] = array_merge($familiares[$indice], $datosFamiliar);
        } else {
            // MODO CREAR:
            // Simplemente agregamos el nuevo familiar al final del array.
            $familiares[] = $datosFamiliar;
        }

        // 5. Actualizamos la sesión
        session(['asistente.familiares' => $familiares]);

        // 6. Volvemos al Hub (Vista 1) usando la ruta de retorno seguro
        return redirect()->route('alumnos.continuar');
    }

    public function validarDniAjax(Request $request): JsonResponse
    {
        $dniIngresado = $request->input('dni');
        $indiceActual = $request->input('indice'); 
        $idPersonaActual = $request->input('fk_id_persona');

        // 1. Validar contra el Alumno (En sesión)
        $alumnoData = session('asistente.alumno', []);
        if (isset($alumnoData['dni']) && $alumnoData['dni'] === $dniIngresado) {
            return response()->json(['valid' => false, 'message' => 'El DNI pertenece al alumno.']);
        }

        // 2. Validar contra otros Familiares (En sesión)
        $familiaresEnSesion = session('asistente.familiares', []);
        foreach ($familiaresEnSesion as $k => $familiar) {
            // Si estamos editando, nos saltamos a nosotros mismos
            if (is_numeric($indiceActual) && $k == $indiceActual) {
                continue; 
            }
            if (isset($familiar['dni']) && $familiar['dni'] === $dniIngresado) {
                return response()->json(['valid' => false, 'message' => 'Este DNI ya fue asignado a un familiar en esta carga.']);
            }
        }

        // 3. Validar contra la Base de Datos
        $personaEnBBDD = \App\Models\Persona::where('dni', $dniIngresado)->first();
        if ($personaEnBBDD) {
            // Si el ID encontrado es DISTINTO al actual, es duplicado
            if ($idPersonaActual != $personaEnBBDD->id_persona) {
                return response()->json(['valid' => false, 'message' => 'DNI ya registrado en el sistema.']);
            }
        }

        // Si pasó todo, es válido
        return response()->json(['valid' => true]);
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