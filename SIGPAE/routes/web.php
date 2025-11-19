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
Route::get('/planDeAccion', function () {
    return view('planDeAccion.principal');
})->middleware('auth')->name('planDeAccion.principal');

Route::get('/planDeAccion/crear', function () {
    return view('planDeAccion.crear-editar');
})->middleware('auth')->name('planDeAccion.crear-editar');

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
Route::post('/alumnos/store', [AlumnoController::class, 'store'])->name('alumnos.store');
Route::put('/alumnos/{id}', [AlumnoController::class, 'actualizar'])->name('alumnos.actualizar');

Route::post('alumnos/{id}/cambiar-estado', [AlumnoController::class, 'cambiarActivo'])->name('alumnos.cambiarActivo');



//Rutas familiares
use App\Http\Controllers\FamiliarController;
Route::get('/familiares/crear', [FamiliarController::class, 'crear'])->name('familiares.crear');
Route::get('/familiares/{indice}/editar', [FamiliarController::class, 'editar'])->name('familiares.editar');
Route::post('/familiares/guardar', [FamiliarController::class, 'guardar'])->name('familiares.guardar');
Route::post('/familiares/validar-dni', [FamiliarController::class, 'validarDniAjax'])->name('familiares.validar-dni');
Route::post('/familiares/store-and-return', [FamiliarController::class, 'storeAndReturn'])->name('familiares.storeAndReturn');
Route::delete('/familiares/temp/{index}', [FamiliarController::class, 'removeTempFamiliar'])->name('familiares.removeTemp');

// Búsqueda de alumnos (para seleccionar hermano)
Route::get('/api/alumnos/buscar', [AlumnoController::class, 'buscar'])->name('alumnos.buscar');

// Rutas de los usuarios (profesionales)
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