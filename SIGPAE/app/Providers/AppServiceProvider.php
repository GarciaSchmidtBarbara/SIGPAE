<?php

namespace App\Providers;
// IMPORTS
//Persona
use App\Repositories\Interfaces\PersonaRepositoryInterface;
use App\Services\Interfaces\PersonaServiceInterface;
use App\Services\Implementations\PersonaService;
use App\Repositories\Eloquent\PersonaRepository;
// Profesional
use App\Repositories\Interfaces\ProfesionalRepositoryInterface;
use App\Services\Interfaces\ProfesionalServiceInterface;
use App\Services\Implementations\ProfesionalService;
use App\Repositories\Eloquent\ProfesionalRepository;
// Alumno
use App\Repositories\Interfaces\AlumnoRepositoryInterface;
use App\Services\Interfaces\AlumnoServiceInterface;
use App\Services\Implementations\AlumnoService;
use App\Repositories\Eloquent\AlumnoRepository;
// Familiar
use App\Repositories\Interfaces\FamiliarRepositoryInterface;
use App\Services\Interfaces\FamiliarServiceInterface;
use App\Services\Implementations\FamiliarService;
use App\Repositories\Eloquent\FamiliarRepository;
// PlanDeAccion
use App\Repositories\Interfaces\PlanDeAccionRepositoryInterface;
use App\Services\Interfaces\PlanDeAccionServiceInterface;
use App\Services\Implementations\PlanDeAccionService;
use App\Repositories\Eloquent\PlanDeAccionRepository;
// Support
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
// Models
use App\Models\Profesional; 
//Intervencion
use App\Services\Interfaces\IntervencionServiceInterface;
use App\Services\Implementations\IntervencionService;
use App\Repositories\Interfaces\IntervencionRepositoryInterface;
use App\Repositories\Eloquent\IntervencionRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {
        // Repositories
        $this->app->bind(AlumnoRepositoryInterface::class, AlumnoRepository::class);
        $this->app->bind(FamiliarRepositoryInterface::class, FamiliarRepository::class);
        $this->app->bind(PersonaRepositoryInterface::class, PersonaRepository::class);
        $this->app->bind(ProfesionalRepositoryInterface::class, ProfesionalRepository::class);
        $this->app->bind(IntervencionRepositoryInterface::class,IntervencionRepository::class
    );
        $this->app->bind(PlanDeAccionRepositoryInterface::class, PlanDeAccionRepository::class);

        // Services
        $this->app->bind(AlumnoServiceInterface::class, AlumnoService::class);
        $this->app->bind(FamiliarServiceInterface::class, FamiliarService::class);
        $this->app->bind(PersonaServiceInterface::class, PersonaService::class);
        $this->app->bind(ProfesionalServiceInterface::class, ProfesionalService::class);
        $this->app->bind(IntervencionServiceInterface::class,IntervencionService::class
    );
        $this->app->bind(PlanDeAccionServiceInterface::class, PlanDeAccionService::class);
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