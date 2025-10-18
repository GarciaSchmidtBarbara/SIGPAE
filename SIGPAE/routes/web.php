<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\LoginController;


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
})->name('planillas.principal');

//Ruta Perfil
Route::get('/perfil', function(){
    return view('perfil.principal');
})->name('perfil.principal');