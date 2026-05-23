<?php

namespace App\Services;

use App\Exceptions\Business\PlanificacionException;
use App\Models\EstadoAnual;
use App\Models\EstadoDiaria;
use App\Repositories\Contracts\DocenteRepositoryInterface;
use App\Repositories\Contracts\PlanificacionRepositoryInterface;
use App\Services\Contracts\PlanificacionServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PlanificacionService implements PlanificacionServiceInterface
{
    // ── Constantes de Estado ──────────────────────────────────────────
    public const ESTADO_BORRADOR    = 'BORRADOR';
    public const ESTADO_EN_REVISION = 'EN_REVISION';
    public const ESTADO_APROBADO    = 'APROBADO';

    public function __construct(
        private readonly PlanificacionRepositoryInterface $planAnualRepo,
        private readonly PlanificacionRepositoryInterface $planDiariaRepo,
        private readonly DocenteRepositoryInterface $docenteRepo,
    ) {}

    // ─────────────────────────────────────────────────────────────────
    // CREAR PLANIFICACIÓN ANUAL
    // ─────────────────────────────────────────────────────────────────

    public function crearPlanificacionAnual(
        array $data,
        int $docenteId
    ): object {
        return DB::transaction(function () use ($data, $docenteId) {

            // ── Validación 1: El docente está asignado al cursado ────
            $this->validarDocenteAsignado(
                $docenteId,
                $data['persona_cargo_cursado_id']
            );

            // ── Validación 2: Unicidad pedagógica anual ──────────────
            $this->validarUnicidadAnual(
                $data['persona_cargo_cursado_id'],
                $data['areas_id']
            );

            // ── Crear la planificación ───────────────────────────────
            $planificacion = $this->planAnualRepo->create($data);

            // ── Registrar estado inicial BORRADOR ────────────────────
            EstadoAnual::create([
                'estado'                 => self::ESTADO_BORRADOR,
                'fecha'                  => now()->toDateString(),
                'planificacion_anual_id' => $planificacion->id,
            ]);

            return $this->planAnualRepo->findById($planificacion->id);
        });
    }

    // ─────────────────────────────────────────────────────────────────
    // CREAR PLANIFICACIÓN DIARIA
    // ─────────────────────────────────────────────────────────────────

    public function crearPlanificacionDiaria(
        array $data,
        int $docenteId
    ): object {
        return DB::transaction(function () use ($data, $docenteId) {

            // ── Validación 1: Docente asignado ───────────────────────
            $asignacion = $this->validarDocenteAsignado(
                $docenteId,
                $data['persona_cargo_cursado_id']
            );

            // ── Validación 2: Fecha dentro del período lectivo ───────
            $this->validarFechaEnPeriodoLectivo(
                $data['fecha_estimada'],
                $asignacion
            );

            // ── Validación 3: No duplicar fecha en el mismo cursado ──
            $this->validarFechaNoDuplicada(
                $data['fecha_estimada'],
                $data['persona_cargo_cursado_id']
            );

            // ── Crear la planificación ───────────────────────────────
            $planificacion = $this->planDiariaRepo->create($data);

            // ── Registrar estado inicial BORRADOR ────────────────────
            EstadoDiaria::create([
                'estado'                  => self::ESTADO_BORRADOR,
                'fecha'                   => now()->toDateString(),
                'planificacion_diaria_id' => $planificacion->id,
            ]);

            return $this->planDiariaRepo->findById($planificacion->id);
        });
    }

    // ─────────────────────────────────────────────────────────────────
    // ENVIAR A REVISIÓN
    // ─────────────────────────────────────────────────────────────────

    public function enviarARevision(
        int $planificacionId,
        int $docenteId
    ): bool {
        return DB::transaction(function () use ($planificacionId, $docenteId) {

            // ── Obtener la planificación ─────────────────────────────
            $planificacion = $this->planAnualRepo->findById($planificacionId);

            if (!$planificacion) {
                throw PlanificacionException::planificacionNoEncontrada(
                    $planificacionId
                );
            }

            // ── Verificar que el docente es el dueño ─────────────────
            $this->validarDocenteEsDueno($planificacion, $docenteId);

            // ── Obtener estado actual ────────────────────────────────
            $estadoActual = $planificacion->estados()
                ->latest('fecha')
                ->first()
                ?->estado ?? self::ESTADO_BORRADOR;

            // ── Validar que está en BORRADOR ─────────────────────────
            if ($estadoActual !== self::ESTADO_BORRADOR) {
                throw PlanificacionException::documentoBloqueado();
            }

            // ── Transicionar a EN_REVISION ───────────────────────────
            EstadoAnual::create([
                'estado'                 => self::ESTADO_EN_REVISION,
                'fecha'                  => now()->toDateString(),
                'planificacion_anual_id' => $planificacionId,
            ]);

            return true;
        });
    }

    // ─────────────────────────────────────────────────────────────────
    // MODIFICAR PLANIFICACIÓN
    // ─────────────────────────────────────────────────────────────────

    public function modificarPlanificacion(
        int $planificacionId,
        array $data,
        int $docenteId
    ): object {
        return DB::transaction(function () use (
            $planificacionId,
            $data,
            $docenteId
        ) {
            // ── Obtener la planificación ─────────────────────────────
            $planificacion = $this->planAnualRepo->findById($planificacionId);

            if (!$planificacion) {
                throw PlanificacionException::planificacionNoEncontrada(
                    $planificacionId
                );
            }

            // ── Verificar dueño ──────────────────────────────────────
            $this->validarDocenteEsDueno($planificacion, $docenteId);

            // ── Verificar estado ─────────────────────────────────────
            $estadoActual = $planificacion->estados()
                ->latest('fecha')
                ->first()
                ?->estado ?? self::ESTADO_BORRADOR;

            if ($estadoActual === self::ESTADO_EN_REVISION) {
                throw PlanificacionException::documentoBloqueado();
            }

            if ($estadoActual === self::ESTADO_APROBADO) {
                throw PlanificacionException::documentoInmutable(
                    self::ESTADO_APROBADO
                );
            }

            return $this->planAnualRepo->update($planificacionId, $data);
        });
    }

    // ─────────────────────────────────────────────────────────────────
    // OBTENER PLANIFICACIONES DEL DOCENTE
    // ─────────────────────────────────────────────────────────────────

    public function obtenerPlanificacionesDocente(int $docenteId): Collection
    {
        return collect(
            $this->planAnualRepo->getByDocente($docenteId)
        );
    }

    // ─────────────────────────────────────────────────────────────────
    // MÉTODOS PRIVADOS DE VALIDACIÓN
    // ─────────────────────────────────────────────────────────────────

    /**
     * Valida que el docente esté asignado al cursado.
     * Retorna la asignación para reutilizarla en otras validaciones.
     */
    private function validarDocenteAsignado(
        int $docenteId,
        int $personaCargoCursadoId
    ): object {
        $cursados = $this->docenteRepo->getCursadosByDocente($docenteId);

        $asignacion = $cursados->firstWhere('id', $personaCargoCursadoId);

        if (!$asignacion) {
            throw PlanificacionException::docenteNoAsignado(
                $docenteId,
                $personaCargoCursadoId
            );
        }

        return $asignacion;
    }

    /**
     * Valida unicidad pedagógica anual:
     * No puede haber otra planificación activa
     * para el mismo cursado y área en EN_REVISION o APROBADO.
     */
    private function validarUnicidadAnual(
        int $personaCargoCursadoId,
        int $areaId
    ): void {
        $existentes = $this->planAnualRepo
            ->getByAsignacion($personaCargoCursadoId)
            ->filter(function ($plan) use ($areaId) {

                if ($plan->areas_id !== $areaId) {
                    return false;
                }

                $estado = $plan->estados()
                    ->latest('fecha')
                    ->first()
                    ?->estado;

                return in_array($estado, [
                    self::ESTADO_EN_REVISION,
                    self::ESTADO_APROBADO,
                ]);
            });

        if ($existentes->isNotEmpty()) {
            throw PlanificacionException::yaExistePlanificacionActiva(
                $personaCargoCursadoId,
                $areaId
            );
        }
    }

    /**
     * Valida que la fecha esté dentro del período lectivo del cursado.
     */
    private function validarFechaEnPeriodoLectivo(
        string $fecha,
        object $asignacion
    ): void {
        $cursado     = $asignacion->cursado;
        $fechaInicio = $cursado->fecha_inicio->format('Y-m-d');
        $fechaFin    = $cursado->fecha_fin->format('Y-m-d');

        if ($fecha < $fechaInicio || $fecha > $fechaFin) {
            throw PlanificacionException::fechaFueraDelPeriodoLectivo(
                $fecha,
                $fechaInicio,
                $fechaFin
            );
        }
    }

    /**
     * Valida que no exista otra planificación diaria
     * para la misma fecha y cursado.
     */
    private function validarFechaNoDuplicada(
        string $fecha,
        int $personaCargoCursadoId
    ): void {
        $existe = $this->planDiariaRepo
            ->getByAsignacion($personaCargoCursadoId)
            ->contains('fecha_estimada', $fecha);

        if ($existe) {
            throw PlanificacionException::fechaDuplicadaEnCursado(
                $fecha,
                $personaCargoCursadoId
            );
        }
    }

    /**
     * Valida que el docente sea el dueño de la planificación.
     */
    private function validarDocenteEsDueno(
        object $planificacion,
        int $docenteId
    ): void {
        $idDocente = $planificacion
            ->personaCargoCursado
            ?->personaCargo
            ?->personas_id;

        if ($idDocente !== $docenteId) {
            throw PlanificacionException::docenteNoAsignado(
                $docenteId,
                $planificacion->persona_cargo_cursado_id
            );
        }
    }
}
