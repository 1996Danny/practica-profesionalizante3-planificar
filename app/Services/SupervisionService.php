<?php

namespace App\Services;

use App\Exceptions\Business\SupervisionException;
use App\Models\EstadoAnual;
use App\Models\PersonaCargo;
use App\Models\PersonaCargoCursado;
use App\Repositories\Contracts\DirectorRepositoryInterface;
use App\Repositories\Contracts\PlanificacionRepositoryInterface;
use App\Services\Contracts\SupervisionServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SupervisionService implements SupervisionServiceInterface
{
    // ── Estados válidos para el Director ─────────────────────────────
    private const ESTADOS_VALIDOS_DIRECTOR = [
        PlanificacionService::ESTADO_APROBADO,
        PlanificacionService::ESTADO_BORRADOR,
    ];

    public function __construct(
        private readonly DirectorRepositoryInterface $directorRepo,
        private readonly PlanificacionRepositoryInterface $planAnualRepo,
    ) {}

    // ─────────────────────────────────────────────────────────────────
    // EVALUAR PLANIFICACIÓN
    // ─────────────────────────────────────────────────────────────────

    /**
     * Transiciona el estado de una planificación.
     *
     * EN_REVISION → APROBADO  (director aprueba)
     * EN_REVISION → BORRADOR  (director rechaza con observaciones)
     */
    public function evaluarPlanificacion(
        int $planificacionId,
        int $directorId,
        string $nuevoEstado,
        ?string $observaciones = null
    ): bool {
        return DB::transaction(function () use (
            $planificacionId,
            $directorId,
            $nuevoEstado,
            $observaciones
        ) {
            // ── Validar que el estado destino es válido ───────────────
            if (!in_array($nuevoEstado, self::ESTADOS_VALIDOS_DIRECTOR)) {
                throw SupervisionException::estadoInvalidoParaDirector(
                    $nuevoEstado
                );
            }

            // ── Obtener la planificación ─────────────────────────────
            $planificacion = $this->planAnualRepo->findById($planificacionId);

            if (!$planificacion) {
                throw SupervisionException::planificacionNoEnRevision(
                    $planificacionId
                );
            }

            // ── Validar que está en EN_REVISION ──────────────────────
            $estadoActual = $planificacion->estados()
                ->latest('fecha')
                ->first()
                ?->estado;

            if ($estadoActual !== PlanificacionService::ESTADO_EN_REVISION) {
                throw SupervisionException::planificacionNoEnRevision(
                    $planificacionId
                );
            }

            // ── Si rechaza: observaciones obligatorias ───────────────
            if (
                $nuevoEstado === PlanificacionService::ESTADO_BORRADOR
                && empty($observaciones)
            ) {
                throw SupervisionException::observacionesObligatorias();
            }

            // ── Registrar nuevo estado ───────────────────────────────
            EstadoAnual::create([
                'estado'                 => $nuevoEstado,
                'fecha'                  => now()->toDateString(),
                'planificacion_anual_id' => $planificacionId,
            ]);

            return true;
        });
    }

    // ─────────────────────────────────────────────────────────────────
    // OBTENER PENDIENTES DE REVISIÓN
    // ─────────────────────────────────────────────────────────────────

    /**
     * Retorna planificaciones en estado EN_REVISION.
     * El director ve TODAS (encapsulamiento institucional).
     */
    public function obtenerPendientesRevision(
        int $directorId,
        string $tipo = 'todas'
    ): Collection {
        return collect(
            $this->directorRepo->getPlanificacionesPendientes($tipo)
        );
    }

    // ─────────────────────────────────────────────────────────────────
    // RESUMEN DE ESTADOS
    // ─────────────────────────────────────────────────────────────────

    /**
     * Retorna conteo de planificaciones agrupado por estado.
     * Para el dashboard del director.
     */
    public function obtenerResumenEstados(int $directorId): array
    {
        return $this->directorRepo->getResumenEstados();
    }

    // ─────────────────────────────────────────────────────────────────
    // ASIGNAR DOCENTE SUPLENTE
    // ─────────────────────────────────────────────────────────────────

    /**
     * Asigna un docente suplente a un espacio curricular.
     *
     * Reglas:
     * - NO duplica la planificación existente
     * - Crea nuevo PersonaCargo con sit_revista Suplente
     * - Crea nuevo PersonaCargoCursado heredando el cursado original
     * - Valida que el suplente no esté ya asignado al cursado
     */
    public function asignarDocenteSuplente(
        int $personaCargoCursadoId,
        int $nuevoDocenteId,
        string $motivo,
        string $fechaInicio,
        ?string $fechaFin = null
    ): bool {
        return DB::transaction(function () use (
            $personaCargoCursadoId,
            $nuevoDocenteId,
            $motivo,
            $fechaInicio,
            $fechaFin
        ) {
            // ── Obtener la asignación original ───────────────────────
            $asignacionOriginal = PersonaCargoCursado::with([
                'personaCargo',
                'cursado',
            ])->findOrFail($personaCargoCursadoId);

            $cursadoId = $asignacionOriginal->cursados_id;
            $cargoId   = $asignacionOriginal->personaCargo->cargos_id;

            // ── Verificar que el suplente no esté ya asignado ────────
            $yaAsignado = PersonaCargoCursado::whereHas(
                'personaCargo',
                function ($q) use ($nuevoDocenteId) {
                    $q->where('personas_id', $nuevoDocenteId);
                }
            )
                ->where('cursados_id', $cursadoId)
                ->exists();

            if ($yaAsignado) {
                throw SupervisionException::docenteSuplenteDuplicado(
                    $cursadoId,
                    $nuevoDocenteId
                );
            }

            // ── Crear PersonaCargo para el suplente ──────────────────
            // sit_revista_id = 3 corresponde a 'Suplente' en tu BD
            $nuevoPersonaCargo = PersonaCargo::create([
                'personas_id'    => $nuevoDocenteId,
                'cargos_id'      => $cargoId,
                'sit_revista_id' => 3,
            ]);

            // ── Crear PersonaCargoCursado heredando el cursado ────────
            PersonaCargoCursado::create([
                'persona_cargos_id' => $nuevoPersonaCargo->id,
                'cursados_id'       => $cursadoId,
            ]);

            return true;
        });
    }
}
