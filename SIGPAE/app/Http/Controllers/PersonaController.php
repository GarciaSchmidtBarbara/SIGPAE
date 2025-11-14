<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\Interfaces\PersonaServiceInterface;


class PersonaController extends Controller
{
    protected $personaService;

    public function __construct(PersonaServiceInterface $personaService)
    {
        $this->personaService = $personaService;
    }


    // En app/Http/Controllers/PersonaController.php

    public function checkDni(Request $request): JsonResponse
    {
        // [CAMBIO 1] Validamos el 'contexto'
        $data = $request->validate([
            'dni' => 'required|string|max:15',
            'edit_index' => 'nullable|numeric',
            'context'    => 'nullable|string|in:alumno,familiar' // Acepta 'alumno', 'familiar', o nada
        ]);

        $dni = $data['dni'];
        $context = $data['context'] ?? 'familiar'; // Si no se envía, asumimos 'familiar'

        // 1. Validar contra BBDD (Siempre se hace)
        $persona = $this->personaService->findPersonaByDni($dni);
        if ($persona) {
            return response()->json(false);
        }

        // 2. Cargar sesiones
        $alumnoTemp = session('alumno_temp', []);
        $familiaresTemp = session('familiares_temp', []);

        // [CAMBIO 2] Lógica de validación basada en el contexto

        if ($context === 'alumno') {
            // --- Soy el ALUMNO, valido contra Familiares ---
            foreach ($familiaresTemp as $familiar) {
                if (!empty($familiar['dni']) && $familiar['dni'] === $dni) {
                    return response()->json(false);
                }
            }
        } 
        else {
            // --- Soy un FAMILIAR, valido contra Alumno y Otros Familiares ---
            $editIndex = $data['edit_index'];

            // a) Validar contra el Alumno
            if (!empty($alumnoTemp['dni']) && $alumnoTemp['dni'] === $dni) {
                return response()->json(false);
            }

            // b) Validar contra Otros Familiares
            foreach ($familiaresTemp as $index => $familiar) {
                if (is_numeric($editIndex) && $index == $editIndex) {
                    continue; // Me salto a mí mismo si estoy editando
                }
                if (!empty($familiar['dni']) && $familiar['dni'] === $dni) {
                    return response()->json(false);
                }
            }
        }

        // Si sobrevive, está OK
        return response()->json(true);
    }
}
