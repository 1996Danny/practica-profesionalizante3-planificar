<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlanificacionDiaria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanificacionDiariaController extends Controller
{
    public function index(): JsonResponse
    {
        $planificaciones = PlanificacionDiaria::with([
            'personaCargoCursado.personaCargo.persona',
            'personaCargoCursado.cursado.curso',
            'estados'
        ])->get();

        return response()->json($planificaciones);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'fecha_estimada'           => 'required|date',
                'fecha_desarrollada'       => 'required|date',
                'fecha_presentacion'       => 'required|date',
                'contenidos_especificos'   => 'required|string',
                'actividades'              => 'required|string',
                'tareas'                   => 'required|string',
                'persona_cargo_cursado_id' => 'required|exists:persona_cargo_cursado,id',
                'tipo_planificacion'       => 'required|string|max:45',
            ]);

            $planificacion = new PlanificacionDiaria();
            $planificacion->fecha_estimada = $validated['fecha_estimada'];
            $planificacion->fecha_desarrollada = $validated['fecha_desarrollada'];
            $planificacion->fecha_presentacion = $validated['fecha_presentacion'];
            $planificacion->contenidos_especificos = $validated['contenidos_especificos'];
            $planificacion->actividades = $validated['actividades'];
            $planificacion->tareas = $validated['tareas'];
            $planificacion->persona_cargo_cursado_id = $validated['persona_cargo_cursado_id'];
            $planificacion->tipo_planificacion = $validated['tipo_planificacion'];
            $planificacion->save();

            return response()->json([
                'Mensaje' => 'Planificación diaria creada correctamente',
                'data' => $planificacion
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'Mensaje' => 'Error de validación',
                'Errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'Mensaje' => 'Error al crear',
                'Error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        $plan = PlanificacionDiaria::with([
            'personaCargoCursado.personaCargo.persona',
            'personaCargoCursado.cursado.curso',
            'estados'
        ])->find($id);

        if (!$plan) {
            return response()->json([
                'message' => 'No encontrada'
            ], 404);
        }

        return response()->json($plan);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $plan = PlanificacionDiaria::find($id);

        if (!$plan) {
            return response()->json([
                'Mensaje' => 'No encontrada'
            ], 404);
        }

        $validated = $request->validate([
            'fecha_estimada'           => 'sometimes|date',
            'fecha_desarrollada'       => 'sometimes|date',
            'fecha_presentacion'       => 'sometimes|date',
            'contenidos_especificos'   => 'sometimes|string',
            'actividades'              => 'sometimes|string',
            'tareas'                   => 'sometimes|string',
            'persona_cargo_cursado_id' => 'sometimes|exists:persona_cargo_cursado,id',
            'tipo_planificacion'       => 'sometimes|string|max:45',
        ]);

        $plan->update($validated);

        return response()->json([
            'Mensaje' => 'Actualizado correctamente',
            'data' => $plan
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $plan = PlanificacionDiaria::find($id);

        if (!$plan) {
            return response()->json([
                'mensaje' => 'No encontrada'
            ], 404);
        }

        $plan->delete();

        return response()->json([
            'Mensaje' => 'Ocultada correctamente'
        ]);
    }
}
