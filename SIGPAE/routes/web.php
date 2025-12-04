<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Planilla;

require __DIR__.'/auth.php';

// Ruta protegida
Route::get('/welcome', function () {
    return view('welcome');
})->middleware('auth')->name('welcome');

Route::post('/probar-post', function () {
    return 'POST recibido correctamente';
});


//Rutas Plan de Acción
use App\Http\Controllers\PlanDeAccionController;
Route::prefix('planes-de-accion')->middleware('auth')->group(function () {
    Route::get('/', [PlanDeAccionController::class, 'vista'])->name('planDeAccion.principal');
    Route::post('/', [PlanDeAccionController::class, 'store'])->name('planDeAccion.store');
    Route::put('/{id}', [PlanDeAccionController::class, 'actualizar'])->name('planDeAccion.actualizar');
    Route::put('/cambiar-activo/{id}', [PlanDeAccionController::class, 'cambiarActivo'])->name('planDeAccion.cambiarActivo');
    Route::delete('/{id}', [PlanDeAccionController::class, 'eliminar'])->name('planDeAccion.eliminar');
    Route::get('/crear', [PlanDeAccionController::class, 'iniciarCreacion'])
    ->name('planDeAccion.iniciar-creacion');
    Route::get('/{id}/editar', [PlanDeAccionController::class, 'iniciarEdicion'])->name('planDeAccion.iniciar-edicion');

});





//Rutas Planillas
// Rutas Planillas (Conectada al Controlador)
use App\Http\Controllers\PlanillaController;
Route::get('/planillas', [PlanillaController::class, 'index'])
    ->middleware('auth')
    ->name('planillas.principal');

// Subrutas de creación de planillas (usadas por planillas.principal)
Route::prefix('planillas')->middleware('auth')->group(function () {

    Route::get('/acta-equipo-indisciplinario/crear', [PlanillaController::class, 'crearActaIndisciplinario'])
        ->name('planillas.acta-equipo-indisciplinario.create');
    Route::post('/acta-equipo-indisciplinario/guardar', [PlanillaController::class, 'guardarActaIndisciplinario'])
        ->name('planillas.acta-equipo-indisciplinario.store');
    // --- ACTA REUNIÓN TRABAJO (EI SIN DIRECTIVOS) ---
    Route::get('/acta-reunion-trabajo/crear', [PlanillaController::class, 'crearActaReunionTrabajo'])
        ->name('planillas.acta-reunion-trabajo.create');
    Route::post('/acta-reunion-trabajo/guardar', [PlanillaController::class, 'guardarActaReunionTrabajo'])
        ->name('planillas.acta-reunion-trabajo.store');
    Route::get('/acta-reuniones-banda/crear', [PlanillaController::class, 'crearActaBanda'])
        ->name('planillas.acta-reuniones-banda.create');

    // 2. Guardar
    Route::post('/acta-reuniones-banda/guardar', [PlanillaController::class, 'guardarActaBanda'])
        ->name('planillas.acta-reuniones-banda.store');

        // --- PLANILLA MEDIAL ---
    Route::get('/planilla-medial/crear', [PlanillaController::class, 'crearPlanillaMedial'])
        ->name('planillas.planilla-medial.create');

    Route::post('/planilla-medial/guardar', [PlanillaController::class, 'guardarPlanillaMedial'])
        ->name('planillas.planilla-medial.store');
    // --- PLANILLA FINAL ---
    Route::get('/planilla-final/crear', [PlanillaController::class, 'crearPlanillaFinal'])
        ->name('planillas.planilla-final.create');

    Route::post('/planilla-final/guardar', [PlanillaController::class, 'guardarPlanillaFinal'])
        ->name('planillas.planilla-final.store');
   
    Route::delete('/planillas/{id}/eliminar', [PlanillaController::class, 'eliminar'])
        ->name('planillas.eliminar');
    Route::get('/planillas/papelera', [PlanillaController::class, 'verPapelera'])
        ->name('planillas.papelera');
    Route::post('/planillas/{id}/restaurar', [PlanillaController::class, 'restaurar'])
        ->name('planillas.restaurar');
    Route::delete('/planillas/{id}/destruir', [PlanillaController::class, 'forzarEliminacion'])
        ->name('planillas.destruir');
    Route::get('/planillas/{id}/ver', [PlanillaController::class, 'ver'])->name('planillas.ver');
    Route::get('/planillas/{id}/editar', [PlanillaController::class, 'editar'])->name('planillas.editar');
    Route::put('/planillas/{id}/actualizar', [PlanillaController::class, 'actualizar'])->name('planillas.actualizar');
    Route::get('/planillas/{id}/descargar', [PlanillaController::class, 'descargar'])->name('planillas.descargar');
    
});

//Rutas Alumnos
use App\Http\Controllers\AlumnoController;
Route::get('/alumnos', [AlumnoController::class, 'vista'])->name('alumnos.principal');
Route::get('/alumnos/crear', [AlumnoController::class, 'crear'])->name('alumnos.crear');
Route::get('/alumnos/{id}/editar', [AlumnoController::class, 'editar'])->name('alumnos.editar');

//con item me refiero a que puede ser un familiar o un hermano alumno
Route::delete('/alumnos/asistente/item/eliminar/{indice}', [AlumnoController::class, 'eliminarItemDeSesion'])->name('asistente.item.eliminar');
Route::post('/alumnos/asistente/sincronizar', [AlumnoController::class, 'sincronizarEstado'])->name('asistente.sincronizar');

Route::get('/alumnos/asistente/continuar', [AlumnoController::class, 'continuar'])->name('alumnos.continuar');
Route::post('/alumnos/validar-dni', [AlumnoController::class, 'validarDniAjax'])->name('alumnos.validar-dni');
Route::get('/api/alumnos/buscar', [AlumnoController::class, 'buscar'])->name('alumnos.buscar');
Route::post('/alumnos/store', [AlumnoController::class, 'guardar'])->name('alumnos.guardar');
Route::put('/alumnos/{id}', [AlumnoController::class, 'actualizar'])->name('alumnos.actualizar');

Route::put('alumnos/{id}/cambiar-estado', [AlumnoController::class, 'cambiarActivo'])->name('alumnos.cambiarActivo');



//Rutas familiares
use App\Http\Controllers\FamiliarController;
Route::get('/familiares/crear', [FamiliarController::class, 'crear'])->name('familiares.crear');
Route::get('/familiares/{indice}/editar', [FamiliarController::class, 'editar'])->name('familiares.editar');
Route::post('/familiares/guardar-y-volver', [FamiliarController::class, 'guardarYVolver'])->name('familiares.guardarYVolver');
Route::post('/familiares/validar-dni', [FamiliarController::class, 'validarDniAjax'])->name('familiares.validar-dni');


//Rutas de los usuarios (profesionales)
use App\Http\Controllers\ProfesionalController;
Route::get('/usuarios', [ProfesionalController::class, 'vista'])->name('usuarios.principal');
Route::get('/usuarios/crear', [ProfesionalController::class, 'crearEditar'])->name('usuarios.crear-editar');
// Crear y actualizar profesionales
Route::post('/usuarios', [ProfesionalController::class, 'store'])->name('usuarios.store');
Route::put('/usuarios/{id}', [ProfesionalController::class, 'update'])->name('usuarios.update');
Route::put('usuarios/{id}/cambiar-estado', [ProfesionalController::class, 'cambiarActivo'])->name('usuarios.cambiarActivo');
Route::get('/usuarios/{id}/editar', [ProfesionalController::class, 'editar'])->name('usuarios.editar');

//Ruta Perfil
use App\Http\Controllers\Auth\PasswordController;
Route::middleware(['auth'])->group(function () {
    Route::get('/perfil', [ProfesionalController::class, 'perfil'])
        ->name('perfil.principal');
    Route::put('/perfil/cambiar-contrasenia', [PasswordController::class, 'update'])
        ->name('perfil.cambiar-contrasenia');
    Route::post('/perfil/actualizar', [ProfesionalController::class, 'actualizarPerfil'])
        ->name('perfil.actualizar');
});

//Ruta Intervenciones
use App\Http\Controllers\IntervencionController;
Route::prefix('intervenciones')->name('intervenciones.')->group(function () {
    Route::get('/', [IntervencionController::class, 'vista'])->name('principal');
    Route::get('/crear', [IntervencionController::class, 'iniciarCreacion'])->name('crear');
    Route::post('/guardar', [IntervencionController::class, 'store'])->name('guardar');
    Route::get('/{id}/editar', [IntervencionController::class, 'iniciarEdicion'])->name('editar');
    Route::put('/{id}', [IntervencionController::class, 'editar'])->name('actualizar');
    Route::delete('/{id}/eliminar', [IntervencionController::class, 'eliminar'])->name('eliminar');
    Route::put('/{id}/cambiar-activo', [IntervencionController::class, 'cambiarActivo'])->name('cambiarActivo');
});

//Rutas Eventos (Calendario)
use App\Http\Controllers\EventoController;
Route::prefix('eventos')->middleware('auth')->name('eventos.')->group(function () {
    // Vistas del CRUD
    Route::get('/vista', [EventoController::class, 'vista'])->name('principal');
    Route::get('/crear', [EventoController::class, 'crear'])->name('crear');
    Route::get('/{id}/ver', [EventoController::class, 'ver'])->name('ver');
    Route::get('/{id}/editar', [EventoController::class, 'editar'])->name('editar');
    Route::post('/guardar', [EventoController::class, 'guardar'])->name('guardar');
    Route::put('/{id}/actualizar', [EventoController::class, 'actualizar'])->name('actualizar');
    Route::get('/crear-derivacion', [EventoController::class, 'crearDerivacion'])->name('crear-derivacion');
    Route::post('/guardar-derivacion', [EventoController::class, 'guardarDerivacion'])->name('guardar-derivacion');
    Route::post('/{id}/confirmar', [EventoController::class, 'actualizarConfirmacion'])->name('actualizar-confirmacion');
    
    // API para calendario
    Route::get('/calendario', [EventoController::class, 'getEventosCalendario'])->name('calendario');
    Route::get('/', [EventoController::class, 'index'])->name('index');
    Route::get('/{id}', [EventoController::class, 'show'])->name('show');
    Route::post('/', [EventoController::class, 'store'])->name('store');
    Route::put('/{id}', [EventoController::class, 'update'])->name('update');
    Route::delete('/{id}', [EventoController::class, 'destroy'])->name('destroy');
});
