<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth; // Importar Auth
use App\Models\Profesional; // Importar tu modelo Profesional
use App\Repositories\Interfaces\AlumnoRepositoryInterface;
use App\Repositories\Eloquent\AlumnoRepository;
use App\Services\Interfaces\AlumnoServiceInterface;
use App\Services\Implementations\AlumnoService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
       $this->app->bind(AlumnoRepositoryInterface::class, AlumnoRepository::class);
        $this->app->bind(AlumnoServiceInterface::class, AlumnoService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. Corregir el tamaño por defecto de strings para bases de datos antiguas (opcional para PostgreSQL)
        Schema::defaultStringLength(191);
        
        // 2. FORZAR EL MODELO DE USUARIO PREDETERMINADO
        // Esto le dice a Laravel que 'Auth::user()' y la mayoría de las referencias a 'User' 
        // deben usar nuestro modelo Profesional.
        Auth::extend('web', function ($app, $name, array $config) {
            return new \Illuminate\Auth\SessionGuard($name, Profesional::class, $app['session.store']);
        });
        
        // Opción más simple y recomendada si no se usa Auth::extend:
        // Asegúrate de que tu modelo Profesional implementa Authenticatable.
    }
}