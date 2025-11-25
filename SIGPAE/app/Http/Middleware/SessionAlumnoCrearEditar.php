<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SessionAlumnoCrearEditar
{
    /**
     * Gestiona la limpieza de la sesión del asistente de Alumno.
     * Si el usuario sale del flujo de trabajo, se borran los datos temporales.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. LISTA BLANCA (La Zona Segura)
        // Todas las rutas que pertenecen al flujo de Crear/Editar Alumno
        $rutasDelModulo = [
            // Vistas Principales (Entradas)
            'alumnos.crear',
            'alumnos.editar',
            'alumnos.continuar', // El retorno seguro

            // Acciones AJAX / Lógica de Alumno
            'asistente.item.eliminar',
            'asistente.sincronizar',
            'alumnos.validar-dni',
            'alumnos.buscar',

            // Acciones de Recarga, logica no AJAX
            'alumnos.cambiarActivo',
            
            // Acciones Finales (Persistencia)
            'alumnos.guardar',
            'alumnos.actualizar',

            // Sub-módulo Familiar (Vista 2)
            'familiares.crear',
            'familiares.editar',
            // Nota: Ajustá este nombre si en tu web.php se llama 'familiares.guardar' o 'guardarYVolver'
            'familiares.guardarYVolver', 
            'familiares.validar-dni',
        ];

        // 2. Obtener la ruta actual
        $rutaActual = $request->route()->getName();

        // 3. EL CHEQUEO
        // Si la ruta actual NO está en la lista blanca...
        if (!in_array($rutaActual, $rutasDelModulo)) {
            
            // ... significa que el usuario se fue a otra parte (Dashboard, Listado, etc.)
            // Limpiamos la memoria temporal.
            session()->forget('asistente');
        }

        return $next($request);
    }
}