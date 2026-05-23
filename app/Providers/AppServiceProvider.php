<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;

// ── Controladores ─────────────────────────────────────────────────
use App\Http\Controllers\Api\PlanificacionAnualController;
use App\Http\Controllers\Api\PlanificacionDiariaController;

// ── Contratos de Repositorios ─────────────────────────────────────
use App\Repositories\Contracts\PlanificacionRepositoryInterface;

// ── Contratos de Servicios ────────────────────────────────────────
use App\Services\Contracts\PlanificacionServiceInterface;
use App\Services\Contracts\SupervisionServiceInterface;

// ── Implementaciones de Servicios ─────────────────────────────────
use App\Services\PlanificacionService;
use App\Services\SupervisionService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ── Binding contextual de Repositorios ────────────────────────
        // Cuando PlanificacionAnualController pida la interfaz
        // le damos el repositorio de planificaciones anuales
        $this->app->when(PlanificacionAnualController::class)
            ->needs(PlanificacionRepositoryInterface::class)
            ->give(fn() => app('planificacion.anual'));

        // Cuando PlanificacionDiariaController pida la interfaz
        // le damos el repositorio de planificaciones diarias
        $this->app->when(PlanificacionDiariaController::class)
            ->needs(PlanificacionRepositoryInterface::class)
            ->give(fn() => app('planificacion.diaria'));

        // ── Binding contextual de PlanificacionService ────────────────
        // El servicio necesita DOS repositorios de planificacion:
        // uno para anuales y otro para diarias
        $this->app->when(PlanificacionService::class)
            ->needs(PlanificacionRepositoryInterface::class)
            ->give(fn() => app('planificacion.anual'));

        // ── Binding de Servicios ──────────────────────────────────────
        $this->app->bind(
            PlanificacionServiceInterface::class,
            PlanificacionService::class
        );

        $this->app->bind(
            SupervisionServiceInterface::class,
            SupervisionService::class
        );
    }

    public function boot(): void {}
}
