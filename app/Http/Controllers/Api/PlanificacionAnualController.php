<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlanificacionAnual;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanificacionAnualController extends Controller
{
    public function index(): JsonResponse
    {
        $planificaciones = PlanificacionAnual::with([
            'area',
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
                'fecha_presentacion'       => 'required|date',
                'aprendizajes_esperados'   => 'required|string',
                'saberes'                  => 'required|string',
                'criterios'                => 'required|string',
                'bibliografia'             => 'required|string',
                'diagnostico'              => 'required|string',
                'areas_id'                 => 'required|exists:areas,id',
                'persona_cargo_cursado_id' => 'required|exists:persona_cargo_cursado,id',
                'tipo_planificacion'       => 'required|string|max:255',
            ]);

            $planificacion = new PlanificacionAnual();
            $planificacion->fecha_presentacion = $validated['fecha_presentacion'];
            $planificacion->aprendizajes_esperados = $validated['aprendizajes_esperados'];
            $planificacion->saberes = $validated['saberes'];
            $planificacion->criterios = $validated['criterios'];
            $planificacion->bibliografia = $validated['bibliografia'];
            $planificacion->diagnostico = $validated['diagnostico'];
            $planificacion->areas_id = $validated['areas_id'];
            $planificacion->persona_cargo_cursado_id = $validated['persona_cargo_cursado_id'];
            $planificacion->tipo_planificacion = $validated['tipo_planificacion'];
            $planificacion->save();

            return response()->json([
                'Mensaje' => 'Planificación anual creada correctamente',
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
        $planificacion = PlanificacionAnual::with([
            'area',
            'personaCargoCursado.personaCargo.persona',
            'personaCargoCursado.cursado.curso',
            'estados'
        ])->find($id);

        if (!$planificacion) {
            return response()->json([
                'message' => 'Planificación no encontrada'
            ], 404);
        }

        return response()->json($planificacion);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $planificacion = PlanificacionAnual::find($id);

        if (!$planificacion) {
            return response()->json([
                'Mensaje' => 'Planificación no encontrada'
            ], 404);
        }

        $validated = $request->validate([
            'fecha_presentacion'       => 'sometimes|date',
            'aprendizajes_esperados'   => 'sometimes|string',
            'saberes'                  => 'sometimes|string',
            'criterios'                => 'sometimes|string',
            'bibliografia'             => 'sometimes|string',
            'diagnostico'              => 'sometimes|string',
            'areas_id'                 => 'sometimes|exists:areas,id',
            'persona_cargo_cursado_id' => 'sometimes|exists:persona_cargo_cursado,id',
            'tipo_planificacion'       => 'sometimes|string|max:255',
        ]);

        $planificacion->update($validated);

        return response()->json([
            'Mensaje' => 'Actualizado correctamente',
            'data' => $planificacion
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $planificacion = PlanificacionAnual::find($id);

        if (!$planificacion) {
            return response()->json([
                'mensaje' => 'No encontrada'
            ], 404);
        }

        $planificacion->delete();

        return response()->json([
            'Mensaje' => 'Ocultada correctamente'
        ]);
    }

    public function trashed(): JsonResponse
    {
        $ocultas = PlanificacionAnual::onlyTrashed()
            ->with(['area', 'personaCargoCursado.personaCargo.persona'])
            ->get();

        return response()->json($ocultas);
    }

    public function restore(int $id): JsonResponse
    {
        $planificacion = PlanificacionAnual::onlyTrashed()->find($id);

        if (!$planificacion) {
            return response()->json([
                'mensaje' => 'No encontrada'
            ], 404);
        }

        $planificacion->restore();

        return response()->json([
            'Mensaje' => 'Restaurada correctamente'
        ]);
    }
}
