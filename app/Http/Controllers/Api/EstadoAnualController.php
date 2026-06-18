<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EstadoAnual;
use App\Models\PlanificacionAnual;
use Illuminate\Http\JsonResponse;

class EstadoAnualController extends Controller
{
    /**
     * GET /api/planificaciones/anuales/{id}/estados
     * Ver historial completo de estados
     */
    public function index(int $id): JsonResponse
    {
        $planificacion = PlanificacionAnual::find($id);

        if (!$planificacion) {
            return response()->json([
                'message' => 'Planificación no encontrada'
            ], 404);
        }

        $estados = EstadoAnual::where('planificacion_anual_id', $id)
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'planificacion_id' => $id,
            'estado_actual'    => $estados->first()?->estado ?? 'SIN_ESTADO',
            'historial'        => $estados
        ]);
    }

    /**
     * POST /api/planificaciones/anuales/{id}/estados/enviar-revision
     * Docente envía a revisión
     */
    public function enviarRevision(int $id): JsonResponse
    {
        $planificacion = PlanificacionAnual::find($id);

        if (!$planificacion) {
            return response()->json([
                'message' => 'Planificación no encontrada'
            ], 404);
        }

        $estadoActual = $planificacion->estados()
            ->latest('fecha')
            ->latest('id')
            ->first();

        $estadoActualNombre = $estadoActual?->estado ?? EstadoAnual::BORRADOR;

        $transicionesPermitidas = EstadoAnual::TRANSICIONES[$estadoActualNombre] ?? [];

        if (!in_array(EstadoAnual::EN_REVISION, $transicionesPermitidas)) {
            return response()->json([
                'message'                 => "No se puede enviar a revisión desde el estado: {$estadoActualNombre}",
                'estado_actual'           => $estadoActualNombre,
                'transiciones_permitidas' => $transicionesPermitidas
            ], 422);
        }

        $nuevoEstado = EstadoAnual::create([
            'estado'                 => EstadoAnual::EN_REVISION,
            'fecha'                  => now()->toDateString(),
            'planificacion_anual_id' => $id,
        ]);

        return response()->json([
            'message'      => 'Planificación enviada a revisión correctamente',
            'estado_nuevo' => $nuevoEstado->estado,
            'fecha'        => $nuevoEstado->fecha,
        ], 201);
    }

    /**
     * POST /api/planificaciones/anuales/{id}/estados/aprobar
     * Director aprueba la planificación
     */
    public function aprobar(int $id): JsonResponse
    {
        $planificacion = PlanificacionAnual::find($id);

        if (!$planificacion) {
            return response()->json([
                'message' => 'Planificación no encontrada'
            ], 404);
        }

        $estadoActual = $planificacion->estados()
            ->latest('fecha')
            ->latest('id')
            ->first();

        $estadoActualNombre = $estadoActual?->estado ?? EstadoAnual::BORRADOR;

        if ($estadoActualNombre !== EstadoAnual::EN_REVISION) {
            return response()->json([
                'message'       => 'Solo se pueden aprobar planificaciones en EN_REVISION',
                'estado_actual' => $estadoActualNombre
            ], 422);
        }

        $nuevoEstado = EstadoAnual::create([
            'estado'                 => EstadoAnual::APROBADO,
            'fecha'                  => now()->toDateString(),
            'planificacion_anual_id' => $id,
        ]);

        return response()->json([
            'message'      => 'Planificación aprobada correctamente',
            'estado_nuevo' => $nuevoEstado->estado,
            'fecha'        => $nuevoEstado->fecha,
        ], 201);
    }

    /**
     * POST /api/planificaciones/anuales/{id}/estados/rechazar
     * Director rechaza y devuelve a BORRADOR
     */
    public function rechazar(int $id): JsonResponse
    {
        $planificacion = PlanificacionAnual::find($id);

        if (!$planificacion) {
            return response()->json([
                'message' => 'Planificación no encontrada'
            ], 404);
        }

        $estadoActual = $planificacion->estados()
            ->latest('fecha')
            ->latest('id')
            ->first();

        $estadoActualNombre = $estadoActual?->estado ?? EstadoAnual::BORRADOR;

        if ($estadoActualNombre !== EstadoAnual::EN_REVISION) {
            return response()->json([
                'message'       => 'Solo se pueden rechazar planificaciones en EN_REVISION',
                'estado_actual' => $estadoActualNombre
            ], 422);
        }

        $nuevoEstado = EstadoAnual::create([
            'estado'                 => EstadoAnual::BORRADOR,
            'fecha'                  => now()->toDateString(),
            'planificacion_anual_id' => $id,
        ]);

        return response()->json([
            'message'      => 'Planificación rechazada. Vuelve al estado BORRADOR para correcciones',
            'estado_nuevo' => $nuevoEstado->estado,
            'fecha'        => $nuevoEstado->fecha,
        ], 201);
    }
}
