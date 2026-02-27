<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Planilla;

require __DIR__.'/auth.php';

Route::get('/', function () {
    return auth()->check() ? redirect('/welcome') : redirect('/login');
})->name('home');

// Ruta protegida
use App\Http\Controllers\HomeController;
Route::get('/welcome', [HomeController::class, 'index'])
    ->middleware('auth')
    ->name('welcome');

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
    Route::post('/{id}/subir-documento', [PlanDeAccionController::class, 'subirDocumento'])->name('planDeAccion.subirDocumento');

});

use App\Http\Controllers\PlanillaController;

// Lo dejo todo unificado en el mismo grupo
Route::prefix('planillas')->middleware('auth')->name('planillas.')->group(function () {

    // principal
    Route::get('/', [PlanillaController::class, 'index'])->name('principal');

    // ACTAS
    Route::get('/acta-equipo-indisciplinario/crear', [PlanillaController::class, 'crearActaIndisciplinario'])
        ->name('acta-equipo-indisciplinario.create');
    Route::post('/acta-equipo-indisciplinario/guardar', [PlanillaController::class, 'guardarActaIndisciplinario'])
        ->name('acta-equipo-indisciplinario.store');

    Route::get('/acta-reunion-trabajo/crear', [PlanillaController::class, 'crearActaReunionTrabajo'])
        ->name('acta-reunion-trabajo.create');
    Route::post('/acta-reunion-trabajo/guardar', [PlanillaController::class, 'guardarActaReunionTrabajo'])
        ->name('acta-reunion-trabajo.store');

    Route::get('/acta-reuniones-banda/crear', [PlanillaController::class, 'crearActaBanda'])
        ->name('acta-reuniones-banda.create');
    Route::post('/acta-reuniones-banda/guardar', [PlanillaController::class, 'guardarActaBanda'])
        ->name('acta-reuniones-banda.store');

    // PLANILLA MEDIAL
    Route::get('/planilla-medial/crear', [PlanillaController::class, 'crearPlanillaMedial'])
        ->name('planilla-medial.create');
    Route::post('/planilla-medial/guardar', [PlanillaController::class, 'guardarPlanillaMedial'])
        ->name('planilla-medial.store');

    // PLANILLA FINAL
    Route::get('/planilla-final/crear', [PlanillaController::class, 'crearPlanillaFinal'])
        ->name('planilla-final.create');
    Route::post('/planilla-final/guardar', [PlanillaController::class, 'guardarPlanillaFinal'])
        ->name('planilla-final.store');

    // CRUD genérico de planillas
    Route::delete('/{id}/eliminar', [PlanillaController::class, 'eliminar'])->name('eliminar');
    Route::get('/papelera', [PlanillaController::class, 'verPapelera'])->name('papelera');
    Route::post('/{id}/restaurar', [PlanillaController::class, 'restaurar'])->name('restaurar');
    Route::delete('/{id}/destruir', [PlanillaController::class, 'forzarEliminacion'])->name('destruir');
    Route::get('/{id}/ver', [PlanillaController::class, 'ver'])->name('ver');
    Route::get('/{id}/editar', [PlanillaController::class, 'editar'])->name('editar');
    Route::put('/{id}/actualizar', [PlanillaController::class, 'actualizar'])->name('actualizar');
    Route::get('/{id}/descargar', [PlanillaController::class, 'descargar'])->name('descargar');
});

//Rutas Alumnos
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\FamiliarController;

// Ruta principal de listado (SIN el middleware)
Route::get('/alumnos', [AlumnoController::class, 'vista'])->name('alumnos.principal');

// Rutas del flujo de creación/edición de alumno (CON el middleware SessionAlumnoCrearEditar)
Route::middleware(['auth', \App\Http\Middleware\SessionAlumnoCrearEditar::class])->group(function () {
    Route::get('/alumnos/crear', [AlumnoController::class, 'crear'])->name('alumnos.crear');
    Route::get('/alumnos/{id}/editar', [AlumnoController::class, 'editar'])->name('alumnos.editar');
    
    // con item me refiero a que puede ser un familiar o un hermano alumno
    Route::delete('/alumnos/asistente/item/eliminar/{indice}', [AlumnoController::class, 'eliminarItemDeSesion'])->name('asistente.item.eliminar');
    Route::post('/alumnos/asistente/sincronizar', [AlumnoController::class, 'sincronizarEstado'])->name('asistente.sincronizar');
    
    Route::get('/alumnos/asistente/continuar', [AlumnoController::class, 'continuar'])->name('alumnos.continuar');
    Route::post('/alumnos/validar-dni', [AlumnoController::class, 'validarDniAjax'])->name('alumnos.validar-dni');
    Route::post('/alumnos/store', [AlumnoController::class, 'guardar'])->name('alumnos.guardar');
    Route::put('/alumnos/{id}', [AlumnoController::class, 'actualizar'])->name('alumnos.actualizar');
    Route::patch('/alumnos/{id}/cambiar-estado', [AlumnoController::class, 'cambiarActivo'])->name('alumnos.cambiarActivo');
    
    // Sub-módulo Familiar
    Route::get('/familiares/crear', [FamiliarController::class, 'crear'])->name('familiares.crear');
    Route::get('/familiares/{indice}/editar', [FamiliarController::class, 'editar'])->name('familiares.editar');
    Route::get('/familiares/buscar', [FamiliarController::class, 'buscar'])->name('familiares.buscar');
    Route::post('/familiares/guardar-y-volver', [FamiliarController::class, 'guardarYVolver'])->name('familiares.guardarYVolver');
    Route::post('/familiares/validar-dni', [FamiliarController::class, 'validarDniAjax'])->name('familiares.validar-dni');
});

// Rutas sin el middleware del asistente
Route::get('/api/alumnos/buscar', [AlumnoController::class, 'buscar'])->name('alumnos.buscar');
Route::put('alumnos/{id}/cambiar-estado', [AlumnoController::class, 'cambiarActivo'])->name('alumnos.cambiarActivo');

// Documentos de alumno (AJAX, sin sesión de asistente)
Route::middleware('auth')->group(function () {
    Route::post('/alumnos/{id}/subir-documento', [AlumnoController::class, 'subirDocumento'])->name('alumnos.subirDocumento');
    Route::delete('/alumnos/{id}/documento/{docId}', [AlumnoController::class, 'eliminarDocumento'])->name('alumnos.eliminarDocumento');
});


//Rutas de los usuarios (profesionales)
use App\Http\Controllers\ProfesionalController;
Route::get('/usuarios', [ProfesionalController::class, 'vista'])->name('usuarios.principal');
Route::get('/usuarios/crear', [ProfesionalController::class, 'crearEditar'])->name('usuarios.crear-editar');
// Crear y actualizar profesionales
Route::post('/usuarios', [ProfesionalController::class, 'store'])->name('usuarios.store');
Route::put('/usuarios/{id}', [ProfesionalController::class, 'update'])->name('usuarios.update');
Route::patch('/usuarios/{id}/cambiar-estado', [ProfesionalController::class, 'cambiarActivo'])->name('usuarios.cambiarActivo');
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
    Route::post('/{id}/subir-documento', [IntervencionController::class, 'subirDocumento'])->name('subirDocumento');
});


//Rutas OAuth google
use App\Http\Controllers\GoogleCalendarController;

Route::get('auth/google', [GoogleCalendarController::class, 'redirectToGoogle'])
    ->name('google.login');
Route::get('auth/google/callback', [GoogleCalendarController::class, 'handleGoogleCallback']);

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
    Route::get('/{id}/editar-derivacion', [EventoController::class, 'editarDerivacion'])->name('editar-derivacion');
    Route::put('/{id}/actualizar-derivacion', [EventoController::class, 'actualizarDerivacion'])->name('actualizar-derivacion');
    Route::post('/{id}/confirmar', [EventoController::class, 'actualizarConfirmacion'])->name('actualizar-confirmacion');
    
    // API para calendario
    Route::get('/calendario', [EventoController::class, 'getEventosCalendario'])->name('calendario');
    Route::get('/', [EventoController::class, 'index'])->name('index');
    Route::get('/{id}', [EventoController::class, 'show'])->name('show');
    Route::post('/', [EventoController::class, 'store'])->name('store');
    Route::put('/{id}', [EventoController::class, 'update'])->name('update');
    Route::delete('/{id}', [EventoController::class, 'destroy'])->name('destroy');
});

//Notificaciones
use App\Http\Controllers\NotificacionController;
Route::prefix('notificaciones')->middleware('auth')->name('notificaciones.')->group(function () {
    // GET  /notificaciones           → JSON con la lista y el contador de no leídas
    Route::get('/', [NotificacionController::class, 'index'])->name('index');

    // POST /notificaciones/{id}/leer → marca como leída y redirige al recurso
    Route::post('/{id}/leer', [NotificacionController::class, 'marcarYRedirigir'])->name('leer');

    // POST /notificaciones/leer-todas → marca todas como leídas
    Route::post('/leer-todas', [NotificacionController::class, 'marcarTodasLeidas'])->name('leer-todas');
});

// POST /eventos/{id}/dejar-de-recordar → establece periodo_recordatorio = 0
Route::post('/eventos/{id}/dejar-de-recordar', [EventoController::class, 'dejarDeRecordar'])
    ->middleware('auth')
    ->name('eventos.dejar-de-recordar');

// ── Documentos ────────────────────────────────────────────────────
use App\Http\Controllers\DocumentoController;
Route::prefix('documentos')->middleware('auth')->name('documentos.')->group(function () {
    Route::get('/', [DocumentoController::class, 'index'])->name('principal');
    Route::get('/subir', [DocumentoController::class, 'create'])->name('crear');
    Route::post('/', [DocumentoController::class, 'store'])->name('guardar');
    Route::delete('/{id}', [DocumentoController::class, 'destroy'])->name('eliminar');
    Route::get('/{id}/descargar', [DocumentoController::class, 'download'])->name('descargar');
    Route::get('/{id}/ver', [DocumentoController::class, 'preview'])->name('ver');
    // API Ajax para buscar entidades (alumno / plan / intervención)
    Route::get('/api/buscar-entidad', [DocumentoController::class, 'buscarEntidad'])->name('buscar-entidad');
});
