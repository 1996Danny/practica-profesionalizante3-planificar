<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Contratos
use App\Repositories\Contracts\DocenteRepositoryInterface;
use App\Repositories\Contracts\DirectorRepositoryInterface;
use App\Repositories\Contracts\PlanificacionRepositoryInterface;

// Implementaciones
use App\Repositories\Eloquent\EloquentDocenteRepository;
use App\Repositories\Eloquent\EloquentDirectorRepository;
use App\Repositories\Eloquent\EloquentPlanificacionAnualRepository;
use App\Repositories\Eloquent\EloquentPlanificacionDiariaRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /*
         * CASO SIMPLE: Una interfaz → Una implementación
         * No hay ambigüedad, Laravel sabe exactamente qué dar.
         */
        $this->app->bind(
            DocenteRepositoryInterface::class,   // Interfaz
            EloquentDocenteRepository::class     // Implementación
        );

        $this->app->bind(
            DirectorRepositoryInterface::class,
            EloquentDirectorRepository::class
        );

        /*
         * CASO COMPLEJO: Una interfaz → DOS implementaciones
         *
         * No podemos usar bind() simple porque Laravel no sabría
         * cuál de las dos implementaciones dar.
         *
         * Solución: registramos con NOMBRE en lugar de por interfaz.
         * Esto es como ponerle una etiqueta a cada implementación.
         *
         * 'planificacion.anual'  → EloquentPlanificacionAnualRepository
         * 'planificacion.diaria' → EloquentPlanificacionDiariaRepository
         */
        $this->app->bind(
            'planificacion.anual',                       // Nombre/etiqueta
            EloquentPlanificacionAnualRepository::class  // Implementación
        );

        $this->app->bind(
            'planificacion.diaria',
            EloquentPlanificacionDiariaRepository::class
        );
    }

    public function boot(): void {}
}
