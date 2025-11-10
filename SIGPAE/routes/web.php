<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;


// Página de inicio → formulario de login
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login.form');

// Rutas de autenticación
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

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

//Ruta Perfil
Route::get('/perfil', function(){
    return view('perfil.principal');
})->middleware('auth')->name('perfil.principal');
Route::get('/perfil/editar', function(){
    return view('perfil.editar');
})->middleware('auth')->name('perfil.editar');

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

//Rutas de cambio de contraseña con sesión iniciada
Route::get('/change-password', [PasswordController::class, 'showChangePasswordForm'])->middleware('auth');
Route::post('/change-password', [PasswordController::class, 'changePassword'])->middleware('auth')->name('password.change');

//Ruta para restaurar contraseña
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset.form');
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('password.update');

//ruta para token
Route::get('/enter-token', [ForgotPasswordController::class, 'showEnterTokenForm'])->name('password.enterToken');
Route::post('/enter-token', [ForgotPasswordController::class, 'verifyToken'])->name('password.verifyToken');