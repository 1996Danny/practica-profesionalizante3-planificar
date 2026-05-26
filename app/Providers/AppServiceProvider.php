<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        // ──────────────────────────────────────────────────────────────
        // BINDING CONTEXTUAL DE REPOSITORIOS PARA CONTROLADORES
        // ──────────────────────────────────────────────────────────────

        $this->app->when(PlanificacionAnualController::class)
            ->needs(PlanificacionRepositoryInterface::class)
            ->give(fn() => app('planificacion.anual'));

        $this->app->when(PlanificacionDiariaController::class)
            ->needs(PlanificacionRepositoryInterface::class)
            ->give(fn() => app('planificacion.diaria'));

        // ──────────────────────────────────────────────────────────────
        // BINDING CONTEXTUAL DE REPOSITORIOS PARA SERVICIOS
        // ──────────────────────────────────────────────────────────────

        // PlanificacionService necesita el repositorio anual
        $this->app->when(PlanificacionService::class)
            ->needs(PlanificacionRepositoryInterface::class)
            ->give(fn() => app('planificacion.anual'));

        // SupervisionService necesita el repositorio anual
        $this->app->when(SupervisionService::class)
            ->needs(PlanificacionRepositoryInterface::class)
            ->give(fn() => app('planificacion.anual'));

        // ──────────────────────────────────────────────────────────────
        // BINDING DE SERVICIOS (Interface → Implementación)
        // ──────────────────────────────────────────────────────────────

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
