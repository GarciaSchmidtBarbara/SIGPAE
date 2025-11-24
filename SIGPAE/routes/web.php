<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

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
Route::get('/planillas', function () {
    return view('planillas.principal');
})->middleware('auth')->name('planillas.principal');

// Subrutas de creación de planillas (usadas por planillas.principal)
Route::prefix('planillas')->middleware('auth')->group(function () {
    Route::get('/acta-equipo-indisciplinario/crear', function () {
        return view('planillas.acta-equipo-indisciplinario');
    })->name('planillas.acta-equipo-indisciplinario.create');

    Route::get('/acta-reunion-trabajo/crear', function () {
        return view('planillas.acta-reunion-trabajo');
    })->name('planillas.acta-reunion-trabajo.create');

    Route::get('/acta-reuniones-banda/crear', function () {
        return view('planillas.acta-reuniones-banda');
    })->name('planillas.acta-reuniones-banda.create');

    Route::get('/planilla-medial/crear', function () {
        return view('planillas.planilla-medial');
    })->name('planillas.planilla-medial.create');
});


//Rutas Personas
use App\Http\Controllers\PersonaController;
Route::post('/personas/check-dni', [PersonaController::class, 'checkDni'])->name('personas.check-dni');

//Rutas Alumnos
use App\Http\Controllers\AlumnoController;
Route::get('/alumnos/iniciar-creacion', [AlumnoController::class, 'iniciarCreacion'])->name('alumnos.iniciar-creacion');
Route::get('/alumnos', [AlumnoController::class, 'vista'])->name('alumnos.principal');
Route::get('/alumnos/crear', [AlumnoController::class, 'crearEditar'])->name('alumnos.crear-editar');
Route::post('/alumnos', [AlumnoController::class, 'store'])->name('alumnos.store');
Route::match(['POST', 'PUT'], '/alumnos/prepare-familiar', [AlumnoController::class, 'prepareFamiliarCreation'])->name('alumnos.prepare-familiar');
Route::put('alumnos/{id}/cambiar-estado', [AlumnoController::class, 'cambiarActivo'])->name('alumnos.cambiarActivo');
Route::get('/alumnos/{id}/editar', [AlumnoController::class, 'editar'])->name('alumnos.editar');
Route::put('/alumnos/{id}', [AlumnoController::class, 'actualizar'])->name('alumnos.actualizar');



//Rutas familiares
use App\Http\Controllers\FamiliarController;
Route::get('/familiares/crear', [FamiliarController::class, 'create'])->name('familiares.create');
Route::post('/familiares/store-and-return', [FamiliarController::class, 'storeAndReturn'])->name('familiares.storeAndReturn');
Route::delete('/familiares/temp/{index}', [FamiliarController::class, 'removeTempFamiliar'])->name('familiares.removeTemp');

//Búsqueda de alumnos (para seleccionar hermano)
Route::get('/api/alumnos/buscar', [AlumnoController::class, 'buscar'])->name('alumnos.buscar');

//Rutas de los usuarios (profesionales)
use App\Http\Controllers\ProfesionalController;
Route::get('/usuarios', [ProfesionalController::class, 'vista'])->name('usuarios.principal');
Route::get('/usuarios/crear', [ProfesionalController::class, 'crearEditar'])->name('usuarios.crear-editar');


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
    Route::get('/crear', [IntervencionController::class, 'crear'])->name('crear');
    Route::get('/{id}/editar', [IntervencionController::class, 'editar'])->name('editar');
    Route::post('/guardar', [IntervencionController::class, 'guardar'])->name('guardar');
    Route::delete('/{id}/eliminar', [IntervencionController::class, 'eliminar'])->name('eliminar');
    Route::put('/{id}/cambiar-activo', [IntervencionController::class, 'cambiarActivo'])->name('cambiarActivo');
});
