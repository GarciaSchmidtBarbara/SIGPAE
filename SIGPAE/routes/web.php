<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

//Rutas Plan de Acción
Route::get('/planDeAccion', function () {
    return view('planDeAccion.principal');
})->name('planDeAccion.principal');

//Rutas Planillas
Route::get('/planillas', function () {
    return view('planillas.principal');
})->name('planillas.principal');

//Ruta Perfil
Route::get('/perfil', function(){
    return view('perfil.principal');
})->name('perfil.principal');