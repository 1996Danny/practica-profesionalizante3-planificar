<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

interface DirectorRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Obtener todos los directores registrados.
     */
    public function getAll(): EloquentCollection;

    /**
     * Obtener el director activo del sistema.
     */
    public function getDirectorActivo(): ?object;

    /**
     * Obtener todos los docentes supervisados.
     */
    public function getDocentesBajoSupervision(): EloquentCollection;

    /**
     * Obtener planificaciones pendientes de revisión.
     * Tipo: 'anual' | 'diaria' | 'todas'
     */
    public function getPlanificacionesPendientes(string $tipo = 'todas'): Collection;

    /**
     * Obtener resumen estadístico de planificaciones por estado.
     */
    public function getResumenEstados(): array;
}
