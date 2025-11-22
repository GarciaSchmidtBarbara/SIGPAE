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

    public function destroy(int $id): JsonResponse
    {
        if ($this->familiarService->deleteFamiliar($id)) {
            return response()->json(null, 204);
        }
        return response()->json(['message' => 'Familiar no encontrado'], 404);
    }

    public function crear()
    {
        // 1. Recuperamos la sesión
        $familiares = session('asistente.familiares', []);

        // 2. Sacamos la lista de IDs de personas ya uregistradas en la bbdd
        $idsEnUso = collect($familiares)->pluck('fk_id_persona')->filter()->values()->toArray();

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
            'solo_lectura' => false, // por defecto va a ser false en porque se crear familiar
            'idsEnUso' => $idsEnUso
        ]);
    }

    public function editar(int $indice)
    {
        // 1. Recuperar Sesión
        $familiares = session('asistente.familiares', []);

        // 2. Validar Índice
        if (!isset($familiares[$indice])) {
            return redirect()->route('alumnos.continuar')->with('error', 'Familiar no encontrado.');
        }

        $familiarData = $familiares[$indice];
        
        // 3. NORMALIZACIÓN (Aplanado de datos)
        // Hacemos esto PRIMERO para tener todos los IDs y datos a mano
        
        // A. Si viene de BBDD (Estructura anidada)
        if (isset($familiarData['persona'])) {
            // intento usar el dato que ya está en la raíz (Sesión/Editado).
            // si no existe, usamos el dato de la relación 'persona' (BBDD).
            // si no existe, usamos cadena vacía.
            $familiarData['nombre'] = $familiarData['nombre'] ?? $familiarData['persona']['nombre'] ?? '';
            $familiarData['apellido'] = $familiarData['apellido'] ?? $familiarData['persona']['apellido'] ?? '';
            $familiarData['dni'] = $familiarData['dni'] ?? $familiarData['persona']['dni'] ?? '';
            $familiarData['fecha_nacimiento'] = $familiarData['fecha_nacimiento'] ?? $familiarData['persona']['fecha_nacimiento'] ?? '';
            $familiarData['domicilio'] = $familiarData['domicilio'] ?? $familiarData['persona']['domicilio'] ?? '';
            $familiarData['nacionalidad'] = $familiarData['nacionalidad'] ?? $familiarData['persona']['nacionalidad'] ?? '';
            // Extraemos el ID para la lógica de vinculo
            $familiarData['fk_id_persona'] = $familiarData['fk_id_persona'] ?? $familiarData['persona']['id_persona'] ?? null;
        }

        // B. Si viene de BBDD (Aula anidada)
        if (isset($familiarData['aula'])) {
            $familiarData['curso'] = $familiarData['aula']['curso'] ?? ''; 
            $familiarData['division'] = $familiarData['aula']['division'] ?? '';
        }

        // C. Si viene de BBDD (Pivot anidado)
        if (isset($familiarData['pivot'])) {
            $familiarData['observaciones'] = $familiarData['observaciones'] ?? $familiarData['pivot']['observaciones'] ?? '';
        }
        
        $familiarData['curso'] = $familiarData['curso'] ?? '';
        $familiarData['division'] = $familiarData['division'] ?? '';
        $familiarData['observaciones'] = $familiarData['observaciones'] ?? '';

        $idsEnUso = collect($familiares)->pluck('fk_id_persona')->filter()->values()->toArray();
        
        // Excepción: Si me estoy editando a mí mismo, me saco de la lista negra
        $miId = $familiarData['fk_id_persona'] ?? null;
        if ($miId) {
            $idsEnUso = array_diff($idsEnUso, [$miId]);
        }

        if (isset($familiarData['parentesco'])) {
            $familiarData['parentesco'] = strtolower($familiarData['parentesco']);
        }

        $solo_lectura = false;

        // Caso A: Hermano Alumno de BBDD (No tiene 'parentesco')
        if (!isset($familiarData['parentesco'])) {
            $familiarData['parentesco'] = 'hermano';
            $familiarData['asiste_a_institucion'] = true;
            $solo_lectura = true;
        }
        // Caso B: Hermano Alumno de Sesión (Tiene marca 'hermano' Y vínculo ID)
        elseif (($familiarData['parentesco'] ?? '') === 'hermano' && !empty($familiarData['asiste_a_institucion'])) {
            $solo_lectura = true;
        }

        // 6. RETORNO
        return view('familiares.crear-editar', [
            'familiarData' => $familiarData,
            'indice' => $indice,
            'solo_lectura' => $solo_lectura,
            'idsEnUso' => array_values($idsEnUso)
        ]);
    }

    public function guardarYVolver(Request $request)
    {
        //dd($request->all());
        
        $fkPersona = $request->input('fk_id_persona');
        $asiste = 0; // Por defecto, asumimos que no es alumno

        if ($fkPersona) {
            // Buscamos en la tabla 'alumnos' si existe una foranea para esta persona.
            // Si existe, entonces es un "Hermano Alumno" (o un alumno vinculado).
            $esAlumno = \App\Models\Alumno::where('fk_id_persona', $fkPersona)->exists();
            
            if ($esAlumno) {
                $asiste = 1;
            }
        }
        
        // Ahora sí, mergeamos con la seguridad de la BBDD
        $request->merge([
            'asiste_a_institucion' => $asiste,
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

    public function guardar(Request $request): JsonResponse
    {
        try {
            $payload = $request->all();

            $familiar = $this->familiarService->createFamiliar($payload);
            return response()->json($familiar, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function actualizar(Request $request, int $id): JsonResponse
    {
        try {
            $data = $request->all();
            $familiar = $this->familiarService->updateFamiliar($id, $data);
            return response()->json($familiar);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

}