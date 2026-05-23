<?php

namespace App\Services\Contracts;

use Illuminate\Support\Collection;

interface SupervisionServiceInterface
{
    /**
     * Evalúa una planificación:
     * EN_REVISION → APROBADO  (director aprueba)
     * EN_REVISION → BORRADOR  (director rechaza con observaciones)
     *
     * Reglas:
     * - Solo actúa sobre planificaciones en estado EN_REVISION
     * - Observaciones obligatorias al rechazar
     * - Solo puede ejecutarlo el Director
     */
    public function evaluarPlanificacion(
        int $planificacionId,
        int $directorId,
        string $nuevoEstado,
        ?string $observaciones = null
    ): bool;

    /**
     * Retorna planificaciones pendientes de revisión.
     * El director ve TODAS (encapsulamiento institucional).
     * Sin filtro por docente.
     *
     * @param string $tipo 'anual' | 'diaria' | 'todas'
     */
    public function obtenerPendientesRevision(
        int $directorId,
        string $tipo = 'todas'
    ): Collection;

    /**
     * Retorna conteo de planificaciones por estado.
     * Para el dashboard del director.
     */
    public function obtenerResumenEstados(int $directorId): array;

    /**
     * Asigna un docente suplente a un espacio curricular.
     *
     * Reglas:
     * - NO duplica la planificación existente
     * - Crea nuevo PersonaCargo con sit_revista Suplente
     * - Crea nuevo PersonaCargoCursado heredando el cursado
     * - Valida que el suplente no esté ya asignado al cursado
     */
    public function asignarDocenteSuplente(
        int $personaCargoCursadoId,
        int $nuevoDocenteId,
        string $motivo,
        string $fechaInicio,
        ?string $fechaFin = null
    ): bool;
}
