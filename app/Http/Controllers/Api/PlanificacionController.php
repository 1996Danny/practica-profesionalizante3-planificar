<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlanificacionAnual;
use App\Models\PlanificacionDiaria;
use Illuminate\Http\JsonResponse;

class PlanificacionController extends Controller
{
    /**
     * TEST 9: Dashboard unificado - Lista todas las planificaciones
     */
    public function index(): JsonResponse
    {
        $anuales = PlanificacionAnual::with([
            'area',
            'personaCargoCursado.personaCargo.persona',
            'personaCargoCursado.cursado.curso'
        ])->get();

        $diarias = PlanificacionDiaria::with([
            'personaCargoCursado.personaCargo.persona',
            'personaCargoCursado.cursado.curso'
        ])->get();

        return response()->json([
            'anuales' => $anuales,
            'diarias' => $diarias
        ], 200);
    }

    /**
     * TEST 10: Ver una planificación específica
     * Busca primero en anuales, luego en diarias
     */
    public function show(int $id): JsonResponse
    {
        // Buscamos primero en planificaciones anuales
        $plan = PlanificacionAnual::with([
            'area',
            'personaCargoCursado.personaCargo.persona',
            'personaCargoCursado.cursado.curso',
            'estados'
        ])->find($id);

        // Si no está en anuales, buscamos en diarias
        if (!$plan) {
            $plan = PlanificacionDiaria::with([
                'personaCargoCursado.personaCargo.persona',
                'personaCargoCursado.cursado.curso',
                'estados'
            ])->find($id);
        }

        // Si no existe en ninguna tabla
        if (!$plan) {
            return response()->json([
                'message' => 'Planificación no encontrada'
            ], 404);
        }

        return response()->json($plan, 200);
    }
}
