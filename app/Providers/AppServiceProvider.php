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
// Aula
use App\Repositories\Interfaces\AulaRepositoryInterface;
use App\Services\Interfaces\AulaServiceInterface;
use App\Services\Implementations\AulaService;
use App\Repositories\Eloquent\AulaRepository;
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
use Illuminate\Support\Facades\Password;
// Models
use App\Models\Profesional; 
//Intervencion
use App\Services\Interfaces\IntervencionServiceInterface;
use App\Services\Implementations\IntervencionService;
use App\Repositories\Interfaces\IntervencionRepositoryInterface;
use App\Repositories\Eloquent\IntervencionRepository;
// Evento
use App\Services\Interfaces\EventoServiceInterface;
use App\Services\Implementations\EventoService;
use App\Repositories\Interfaces\EventoRepositoryInterface;
use App\Repositories\Eloquent\EventoRepository;
// Notificacion
use App\Services\Interfaces\NotificacionServiceInterface;
use App\Services\Implementations\NotificacionService;
use App\Repositories\Interfaces\NotificacionRepositoryInterface;
use App\Repositories\Eloquent\NotificacionRepository;
// Documento
use App\Services\Interfaces\DocumentoServiceInterface;
use App\Services\Implementations\DocumentoService;
use App\Repositories\Interfaces\DocumentoRepositoryInterface;
use App\Repositories\Eloquent\DocumentoRepository;
// Observers
use App\Models\Evento;
use App\Models\Intervencion;
use App\Models\PlanDeAccion;
use App\Models\EsInvitadoA;
use App\Observers\EventoObserver;
use App\Observers\IntervencionObserver;
use App\Observers\PlanDeAccionObserver;
use App\Observers\EsInvitadoAObserver;
use Illuminate\Database\Eloquent\Model;

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
        $this->app->bind(IntervencionRepositoryInterface::class,IntervencionRepository::class);
        $this->app->bind(PlanDeAccionRepositoryInterface::class, PlanDeAccionRepository::class);
        $this->app->bind(EventoRepositoryInterface::class, EventoRepository::class);
        $this->app->bind(AulaRepositoryInterface::class, AulaRepository::class);
        $this->app->bind(NotificacionRepositoryInterface::class, NotificacionRepository::class);
        $this->app->bind(DocumentoRepositoryInterface::class, DocumentoRepository::class);

        // Services
        $this->app->bind(AlumnoServiceInterface::class, AlumnoService::class);
        $this->app->bind(FamiliarServiceInterface::class, FamiliarService::class);
        $this->app->bind(PersonaServiceInterface::class, PersonaService::class);
        $this->app->bind(ProfesionalServiceInterface::class, ProfesionalService::class);
        $this->app->bind(IntervencionServiceInterface::class,IntervencionService::class);
        $this->app->bind(PlanDeAccionServiceInterface::class, PlanDeAccionService::class);
        $this->app->bind(EventoServiceInterface::class, EventoService::class);
        $this->app->bind(AulaServiceInterface::class, AulaService::class);
        $this->app->bind(NotificacionServiceInterface::class, NotificacionService::class);
        $this->app->bind(DocumentoServiceInterface::class, DocumentoService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Detectar N+1 en desarrollo: lanza excepción si se accede a una
        // relación no cargada con eager loading.
        if (app()->isLocal()) {
            Model::preventLazyLoading();
        }
        
        
        // Esto le dice a Laravel que 'Auth::user()' y la mayoría de las referencias a 'User' 
        // deben usar nuestro modelo Profesional.
       // Auth::extend('web', function ($app, $name, array $config) {
        //    return new \Illuminate\Auth\SessionGuard($name, Profesional::class, $app['session.store']);
        //});
        
        // Observers de notificaciones
        Evento::observe(EventoObserver::class);
        Intervencion::observe(IntervencionObserver::class);
        PlanDeAccion::observe(PlanDeAccionObserver::class);
        EsInvitadoA::observe(EsInvitadoAObserver::class);

        //forzar https para producción
        if (app()->environment('production')) { 
            \URL::forceScheme('https'); 
        }
        
    }
}