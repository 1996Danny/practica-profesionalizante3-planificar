<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EstadoDiaria;
use App\Models\PlanificacionDiaria;
use Illuminate\Http\JsonResponse;

class EstadoDiariaController extends Controller
{
    /**
     * GET /api/planificaciones/diarias/{id}/estados
     */
    public function index(int $id): JsonResponse
    {
        $planificacion = PlanificacionDiaria::find($id);

        if (!$planificacion) {
            return response()->json([
                'message' => 'Planificación no encontrada'
            ], 404);
        }

        $estados = EstadoDiaria::where('planificacion_diaria_id', $id)
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
     * POST /api/planificaciones/diarias/{id}/estados/enviar-revision
     */
    public function enviarRevision(int $id): JsonResponse
    {
        $planificacion = PlanificacionDiaria::find($id);

        if (!$planificacion) {
            return response()->json([
                'message' => 'Planificación no encontrada'
            ], 404);
        }

        $estadoActual = $planificacion->estados()
            ->latest('fecha')
            ->latest('id')
            ->first();

        $estadoActualNombre = $estadoActual?->estado ?? EstadoDiaria::BORRADOR;

        $transicionesPermitidas = EstadoDiaria::TRANSICIONES[$estadoActualNombre] ?? [];

        if (!in_array(EstadoDiaria::EN_REVISION, $transicionesPermitidas)) {
            return response()->json([
                'message'                 => "No se puede enviar a revisión desde: {$estadoActualNombre}",
                'estado_actual'           => $estadoActualNombre,
                'transiciones_permitidas' => $transicionesPermitidas
            ], 422);
        }

        $nuevoEstado = EstadoDiaria::create([
            'estado'                  => EstadoDiaria::EN_REVISION,
            'fecha'                   => now()->toDateString(),
            'planificacion_diaria_id' => $id,
        ]);

        return response()->json([
            'message'      => 'Planificación diaria enviada a revisión',
            'estado_nuevo' => $nuevoEstado->estado,
            'fecha'        => $nuevoEstado->fecha,
        ], 201);
    }

    /**
     * POST /api/planificaciones/diarias/{id}/estados/aprobar
     */
    public function aprobar(int $id): JsonResponse
    {
        $planificacion = PlanificacionDiaria::find($id);

        if (!$planificacion) {
            return response()->json([
                'message' => 'Planificación no encontrada'
            ], 404);
        }

        $estadoActual = $planificacion->estados()
            ->latest('fecha')
            ->latest('id')
            ->first();

        $estadoActualNombre = $estadoActual?->estado ?? EstadoDiaria::BORRADOR;

        if ($estadoActualNombre !== EstadoDiaria::EN_REVISION) {
            return response()->json([
                'message'       => 'Solo se pueden aprobar planificaciones en EN_REVISION',
                'estado_actual' => $estadoActualNombre
            ], 422);
        }

        $nuevoEstado = EstadoDiaria::create([
            'estado'                  => EstadoDiaria::APROBADO,
            'fecha'                   => now()->toDateString(),
            'planificacion_diaria_id' => $id,
        ]);

        return response()->json([
            'message'      => 'Planificación diaria aprobada correctamente',
            'estado_nuevo' => $nuevoEstado->estado,
            'fecha'        => $nuevoEstado->fecha,
        ], 201);
    }

    /**
     * POST /api/planificaciones/diarias/{id}/estados/rechazar
     */
    public function rechazar(int $id): JsonResponse
    {
        $planificacion = PlanificacionDiaria::find($id);

        if (!$planificacion) {
            return response()->json([
                'message' => 'Planificación no encontrada'
            ], 404);
        }

        $estadoActual = $planificacion->estados()
            ->latest('fecha')
            ->latest('id')
            ->first();

        $estadoActualNombre = $estadoActual?->estado ?? EstadoDiaria::BORRADOR;

        if ($estadoActualNombre !== EstadoDiaria::EN_REVISION) {
            return response()->json([
                'message'       => 'Solo se pueden rechazar planificaciones en EN_REVISION',
                'estado_actual' => $estadoActualNombre
            ], 422);
        }

        $nuevoEstado = EstadoDiaria::create([
            'estado'                  => EstadoDiaria::BORRADOR,
            'fecha'                   => now()->toDateString(),
            'planificacion_diaria_id' => $id,
        ]);

        return response()->json([
            'message'      => 'Planificación diaria rechazada. Vuelve a BORRADOR',
            'estado_nuevo' => $nuevoEstado->estado,
            'fecha'        => $nuevoEstado->fecha,
        ], 201);
    }
}
