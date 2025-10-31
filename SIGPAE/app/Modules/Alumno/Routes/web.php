<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Alumno\Http\Controllers\AlumnoController;
use App\Modules\Alumno\Http\Controllers\FamiliarController;
use App\Modules\Alumno\Http\Controllers\AulaController;
use App\Modules\Alumno\Http\Controllers\HermanoController;

Route::prefix('alumnos')->group(function () {
    Route::get('/', [AlumnoController::class, 'index'])->name('alumnos.index'); 
    Route::get('/create', [AlumnoController::class, 'create'])->name('alumnos.create'); //formulario de creación
    Route::post('/', [AlumnoController::class, 'store'])->name('alumnos.store'); //crea alumno+persona
  //  Route::get('/{id}', [AlumnoController::class, 'show'])->name('alumnos.show');
    Route::get('/{id}/edit', [AlumnoController::class, 'edit'])->name('alumnos.edit');
    Route::put('/{id}', [AlumnoController::class, 'update'])->name('alumnos.update');
    Route::delete('/{id}', [AlumnoController::class, 'destroy'])->name('alumnos.destroy');
});

Route::prefix('familiares')->group(function () {
    Route::get('/', [FamiliarController::class, 'index'])->name('familiares.index');
    Route::get('/create', [FamiliarController::class, 'create'])->name('familiares.create');
    Route::post('/', [FamiliarController::class, 'store'])->name('familiares.store');
    Route::get('/{id}', [FamiliarController::class, 'show'])->name('familiares.show');
    Route::get('/{id}/edit', [FamiliarController::class, 'edit'])->name('familiares.edit');
    Route::put('/{id}', [FamiliarController::class, 'update'])->name('familiares.update');
    Route::delete('/{id}', [FamiliarController::class, 'destroy'])->name('familiares.destroy');
});

Route::prefix('aulas')->group(function () {
    Route::get('/', [AulaController::class, 'index'])->name('aulas.index');
    Route::get('/create', [AulaController::class, 'create'])->name('aulas.create');
    Route::post('/', [AulaController::class, 'store'])->name('aulas.store');
    Route::get('/{id}', [AulaController::class, 'show'])->name('aulas.show');
    Route::get('/{id}/edit', [AulaController::class, 'edit'])->name('aulas.edit');
    Route::put('/{id}', [AulaController::class, 'update'])->name('aulas.update');
    Route::delete('/{id}', [AulaController::class, 'destroy'])->name('aulas.destroy');
});

Route::prefix('hermanos')->group(function () {
    Route::get('/', [HermanoController::class, 'index'])->name('hermanos.index');
    Route::get('/create', [HermanoController::class, 'create'])->name('hermanos.create');
    Route::post('/', [HermanoController::class, 'store'])->name('hermanos.store');
    Route::get('/{id}', [HermanoController::class, 'show'])->name('hermanos.show');
    Route::get('/{id}/edit', [HermanoController::class, 'edit'])->name('hermanos.edit');
    Route::put('/{id}', [HermanoController::class, 'update'])->name('hermanos.update');
    Route::delete('/{id}', [HermanoController::class, 'destroy'])->name('hermanos.destroy');
});
