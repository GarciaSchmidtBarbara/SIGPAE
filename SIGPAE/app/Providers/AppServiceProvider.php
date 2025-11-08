<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth; 
use App\Models\Profesional; 
use App\Repositories\Interfaces\AlumnoRepositoryInterface;
use App\Repositories\Eloquent\AlumnoRepository;
use App\Services\Interfaces\AlumnoServiceInterface;
use App\Services\Implementations\AlumnoService;
use App\Services\Interfaces\FamiliarServiceInterface;
use App\Services\Implementations\FamiliarService;
use App\Services\Interfaces\PersonaServiceInterface;
use App\Services\Implementations\PersonaService;
use App\Repositories\Interfaces\FamiliarRepositoryInterface;
use App\Repositories\Eloquent\FamiliarRepository;
use App\Repositories\Interfaces\PersonaRepositoryInterface;
use App\Repositories\Eloquent\PersonaRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Repositories
        $this->app->bind(AlumnoRepositoryInterface::class, AlumnoRepository::class);
        $this->app->bind(FamiliarRepositoryInterface::class, FamiliarRepository::class);
        $this->app->bind(PersonaRepositoryInterface::class, PersonaRepository::class);

        // Services
        $this->app->bind(AlumnoServiceInterface::class, AlumnoService::class);
        $this->app->bind(FamiliarServiceInterface::class, FamiliarService::class);
        $this->app->bind(PersonaServiceInterface::class, PersonaService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        
        
        // Esto le dice a Laravel que 'Auth::user()' y la mayoría de las referencias a 'User' 
        // deben usar nuestro modelo Profesional.
        Auth::extend('web', function ($app, $name, array $config) {
            return new \Illuminate\Auth\SessionGuard($name, Profesional::class, $app['session.store']);
        });
        
    }
}