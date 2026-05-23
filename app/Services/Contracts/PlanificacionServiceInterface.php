<?php

namespace App\Services\Contracts;

use Illuminate\Support\Collection;

interface PlanificacionServiceInterface
{
    /**
     * Crea una planificación anual validando:
     * - El docente está asignado al cursado
     * - No existe otra planificación activa para el mismo
     *   cursado + área (unicidad pedagógica anual)
     * - Estado inicial: BORRADOR
     */
    public function crearPlanificacionAnual(
        array $data,
        int $docenteId
    ): object;

    /**
     * Crea una planificación diaria validando:
     * - El docente está asignado al cursado
     * - La fecha está dentro del período lectivo
     * - No existe otra planificación para la misma fecha y cursado
     * - Estado inicial: BORRADOR
     */
    public function crearPlanificacionDiaria(
        array $data,
        int $docenteId
    ): object;

    /**
     * Transiciona: BORRADOR → EN_REVISION
     * Solo puede ejecutarlo el docente asignado.
     * Bloquea la edición del documento.
     */
    public function enviarARevision(
        int $planificacionId,
        int $docenteId
    ): bool;

    /**
     * Modifica una planificación.
     * Solo permitido en estado BORRADOR.
     * Lanza excepción si está EN_REVISION o APROBADO.
     */
    public function modificarPlanificacion(
        int $planificacionId,
        array $data,
        int $docenteId
    ): object;

    /**
     * Retorna las planificaciones del docente.
     * Aislamiento automático por docenteId.
     */
    public function obtenerPlanificacionesDocente(
        int $docenteId
    ): Collection;
}
