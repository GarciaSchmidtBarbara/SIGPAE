<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
//use App\Http\Controllers\Auth\LoginController;
//use App\Http\Controllers\Auth\PasswordController;
//use App\Http\Controllers\Auth\ForgotPasswordController;


require __DIR__.'/auth.php';
// Página de inicio → formulario de login
//Route::get('/', [LoginController::class, 'showLoginForm'])->name('login.form');

// Rutas de autenticación
//Route::post('/login', [LoginController::class, 'login']);
//Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

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


//Rutas Alumnos
use App\Http\Controllers\AlumnoController;
Route::get('/alumnos', [AlumnoController::class, 'vista'])->name('alumnos.principal');
Route::get('/alumnos/crear', [AlumnoController::class, 'crearEditar'])->name('alumnos.crear-editar');
Route::post('/alumnos', [AlumnoController::class, 'store'])->name('alumnos.store');
Route::post('/alumnos/prepare-familiar', [AlumnoController::class, 'prepareFamiliarCreation'])->name('alumnos.prepare-familiar');
Route::post('alumnos/{id}/cambiar-estado', [AlumnoController::class, 'cambiarActivo'])->name('alumnos.cambiarActivo');
Route::post('/alumnos/store', [AlumnoController::class, 'store'])->name('alumnos.store');


//Rutas familiares
use App\Http\Controllers\FamiliarController;
Route::get('/familiares/crear', [FamiliarController::class, 'create'])->name('familiares.create');
Route::post('/familiares/store-and-return', [FamiliarController::class, 'storeAndReturn'])->name('familiares.storeAndReturn');
Route::delete('/familiares/temp/{index}', [FamiliarController::class, 'removeTempFamiliar'])->name('familiares.removeTemp');

// Búsqueda de alumnos (para seleccionar hermano)
Route::get('/api/alumnos/buscar', [AlumnoController::class, 'buscar'])->name('alumnos.buscar');

// Rutas de los usuarios (profesionales)
use App\Http\Controllers\ProfesionalController;
Route::get('/usuarios', [ProfesionalController::class, 'vista'])->name('usuarios.principal');


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