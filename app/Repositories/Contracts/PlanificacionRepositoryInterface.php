<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface PlanificacionRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Obtener planificaciones filtradas por docente.
     */
    public function getByDocente(int $personaId): Collection;

    /**
     * Obtener planificaciones filtradas por su estado más reciente.
     */
    public function getByEstado(string $estado): Collection;

    /**
     * Obtener planificaciones de un docente con un estado específico.
     */
    public function getByDocenteYEstado(int $personaId, string $estado): Collection;

    /**
     * Obtener planificaciones filtradas por año lectivo.
     */
    public function getByAnioLectivo(string $anioLectivo): Collection;

    /**
     * Obtener planificaciones por asignación (persona_cargo_cursado_id).
     */
    public function getByAsignacion(int $personaCargoCursadoId): Collection;

    /**
     * Obtener el tipo de planificación que gestiona este repositorio.
     * Retorna: 'anual' | 'diaria'
     */
    public function getTipo(): string;
}
