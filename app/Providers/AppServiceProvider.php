<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Controladores que necesitan resolución específica
use App\Http\Controllers\Api\PlanificacionAnualController;
use App\Http\Controllers\Api\PlanificacionDiariaController;

// La interfaz compartida que ambos necesitan
use App\Repositories\Contracts\PlanificacionRepositoryInterface;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /*
         * ─────────────────────────────────────────────────────────
         * BINDING CONTEXTUAL
         * ─────────────────────────────────────────────────────────
         *
         * Problema: Dos controladores piden la misma interfaz
         * pero necesitan implementaciones diferentes.
         *
         * Sintaxis:
         *   when(¿Quién lo pide?)
         *     ->needs(¿Qué está pidiendo?)
         *     ->give(¿Qué le damos?)
         * ─────────────────────────────────────────────────────────
         */

        /*
         * Regla 1:
         * Cuando PlanificacionAnualController pida
         * PlanificacionRepositoryInterface...
         * ...dale el repositorio registrado como 'planificacion.anual'
         * (que es EloquentPlanificacionAnualRepository)
         */
        $this->app->when(PlanificacionAnualController::class)
            ->needs(PlanificacionRepositoryInterface::class)
            ->give(fn() => app('planificacion.anual'));
        //               ↑
        //   app('planificacion.anual') resuelve el binding
        //   que registramos en RepositoryServiceProvider

        /*
         * Regla 2:
         * Cuando PlanificacionDiariaController pida
         * la MISMA interfaz...
         * ...dale el repositorio registrado como 'planificacion.diaria'
         * (que es EloquentPlanificacionDiariaRepository)
         */
        $this->app->when(PlanificacionDiariaController::class)
            ->needs(PlanificacionRepositoryInterface::class)
            ->give(fn() => app('planificacion.diaria'));
    }

    public function boot(): void {}
}
