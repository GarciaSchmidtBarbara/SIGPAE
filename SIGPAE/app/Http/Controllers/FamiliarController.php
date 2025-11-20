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

        if (isset($familiarData['persona'])) {
             $familiarData['nombre'] = $familiarData['persona']['nombre'] ?? '';
             $familiarData['apellido'] = $familiarData['persona']['apellido'] ?? '';
             $familiarData['dni'] = $familiarData['persona']['dni'] ?? '';
             $familiarData['fecha_nacimiento'] = $familiarData['persona']['fecha_nacimiento'] ?? '';
             $familiarData['domicilio'] = $familiarData['persona']['domicilio'] ?? '';
             $familiarData['nacionalidad'] = $familiarData['persona']['nacionalidad'] ?? '';
             
             // La clave para saber que es vinculado:
             $familiarData['fk_id_persona'] = $familiarData['persona']['id_persona'] ?? null;
        }

        if (isset($familiarData['aula'])) {
            $familiarData['curso'] = $familiarData['aula']['curso'] ?? ''; 
            $familiarData['division'] = $familiarData['aula']['division'] ?? '';
        }
        
        // B. Si viene de SESIÓN (ya está aplanado, no hacemos nada,
        //    pero nos aseguramos de que 'curso' y 'division' existan para que no falle la vista)
        $familiarData['curso'] = $familiarData['curso'] ?? '';
        $familiarData['division'] = $familiarData['division'] ?? '';


        // --- 2. LÓGICA DE SOLO LECTURA (La Definitiva) ---
        
        // ¿Es un Hermano Alumno Vinculado?
        // Condición: Tiene un ID de persona vinculado.
        // (No importa si parentesco es null o 'hermano', lo que importa es el vínculo).
        $esVinculado = !empty($familiarData['fk_id_persona']);
        
        if ($esVinculado) {
            $solo_lectura = true;
            // Si venía de BBDD (parentesco null), forzamos 'hermano' para que el radio button se marque.
            if (!isset($familiarData['parentesco'])) {
                $familiarData['parentesco'] = 'hermano';
            }
        } else {
            $solo_lectura = false;
        }

        // tambien le paso el indice para que el formulario de l avista alumnos/crear-editar sepa qué familiar está editando.
        return view('familiares.crear-editar', [
            'familiarData' => $familiarData,
            'indice' => $indice,
            'solo_lectura' => $solo_lectura
        ]);
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
            'curso' => 'nullable|string',
            'curso' => 'nullable|string',
            'division' => 'nullable|string',
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
        
        // CORRECCIÓN: Usamos el ID de la Persona del familiar (no del alumno)
        $idPersonaFamiliar = $request->input('fk_id_persona'); 

        // 1. Validar contra el Alumno (En sesión)
        $alumnoData = session('asistente.alumno', []);
        if (isset($alumnoData['dni']) && $alumnoData['dni'] === $dniIngresado) {
            return response()->json(['valid' => false, 'message' => 'El DNI pertenece al alumno.']);
        }

        // 2. Validar contra otros Familiares (En sesión)
        $indiceActual = $request->input('indice');
        $familiaresEnSesion = session('asistente.familiares', []);
        
        foreach ($familiaresEnSesion as $k => $familiar) {
            if (is_numeric($indiceActual) && $k == $indiceActual) {
                continue; 
            }
            if (isset($familiar['dni']) && $familiar['dni'] === $dniIngresado) {
                return response()->json(['valid' => false, 'message' => 'DNI ya ingresado en esta carga.']);
            }
        }

        // 3. Validar contra la Base de Datos
        $personaEnBBDD = \App\Models\Persona::where('dni', $dniIngresado)->first();

        if ($personaEnBBDD) {
            // Si el DNI existe...
            // Verificamos si le pertenece al familiar que estoy editando.
            if ($idPersonaFamiliar && $idPersonaFamiliar == $personaEnBBDD->id_persona) {
                return response()->json(['valid' => true]); // Es el mismo, todo bien.
            }
            
            // Si no es el mismo, es un duplicado.
            return response()->json(['valid' => false, 'message' => 'DNI ya registrado en el sistema.']);
        }

        return response()->json(['valid' => true]);
    }

}