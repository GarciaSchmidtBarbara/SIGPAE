<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Personas
        $this->app->bind(
            \App\Modules\Personas\Repositories\PersonaRepositoryInterface::class,
            \App\Modules\Personas\Repositories\EloquentPersonaRepository::class
        );

        // Planes
        $this->app->bind(
            \App\Modules\Planes\Repositories\PlanRepositoryInterface::class,
            \App\Modules\Planes\Repositories\EloquentPlanRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Nothing for now
    }
}
