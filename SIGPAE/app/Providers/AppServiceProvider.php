<?php

namespace App\Providers;

use App\Repositories\Interfaces\ProfesionalRepositoryInterface;
use App\Repositories\Interfaces\AlumnoRepositoryInterface;
use App\Services\Interfaces\ProfesionalServiceInterface;
use App\Services\Implementations\ProfesionalService;
use App\Services\Interfaces\AlumnoServiceInterface;
use App\Repositories\Eloquent\ProfesionalRepository;
use App\Repositories\Eloquent\AlumnoRepository;
use App\Services\Implementations\AlumnoService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use App\Models\Profesional;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {
        $this->app->bind(AlumnoRepositoryInterface::class, AlumnoRepository::class);
        $this->app->bind(AlumnoServiceInterface::class, AlumnoService::class);
        $this->app->bind(ProfesionalServiceInterface::class, ProfesionalService::class);
        $this->app->bind(ProfesionalRepositoryInterface::class, ProfesionalRepository::class);
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