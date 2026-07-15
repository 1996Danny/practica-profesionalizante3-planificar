<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlanificacionAnual;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanificacionAnualController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = PlanificacionAnual::with([
            'area',
            'personaCargoCursado.personaCargo.persona',
            'personaCargoCursado.cursado.curso',
            'estados'
        ]);

        if ($user && $user->role === 'docente') {
            $query->whereHas('personaCargoCursado.personaCargo.persona.user', function ($q) use ($user) {
                $q->where('id', $user->id);
            });
        }

        if ($user && $user->role === 'director') {
            $query->whereHas('estados', function ($q) {
                $q->where('estado', '!=', 'Borrador');
            });
        }

        $planificaciones = $query->get();

        return response()->json($planificaciones);
    }

    public function store(Request $request): JsonResponse
{
    try {
        $user = $request->user();

        $validated = $request->validate([
            'fecha_presentacion'       => 'required|date',
            'aprendizajes_esperados'   => 'required|string',
            'saberes'                  => 'required|string',
            'criterios'                => 'required|string',
            'bibliografia'             => 'required|string',
            'diagnostico'              => 'required|string',
            'areas_id'                 => 'required|exists:areas,id',
            'persona_cargo_cursado_id' => 'required|integer',
            'tipo_planificacion'       => 'required|string|max:255',
        ]);

        $finalCargoCursadoId = $validated['persona_cargo_cursado_id'];


        if ($user && $user->role === 'docente') {
            $miAsignacionReal = \Illuminate\Support\Facades\DB::table('persona_cargo_cursado')
                ->join('persona_cargos', 'persona_cargo_cursado.persona_cargos_id', '=', 'persona_cargos.id')
                ->where('persona_cargos.personas_id', $user->persona_id)
                ->select('persona_cargo_cursado.id')
                ->first();

            if ($miAsignacionReal) {
                $finalCargoCursadoId = $miAsignacionReal->id;
            }
        }

        $planificacion = new PlanificacionAnual();
        $planificacion->fecha_presentacion       = $validated['fecha_presentacion'];
        $planificacion->aprendizajes_esperados   = $validated['aprendizajes_esperados'];
        $planificacion->saberes                  = $validated['saberes'];
        $planificacion->criterios                = $validated['criterios'];
        $planificacion->bibliografia             = $validated['bibliografia'];
        $planificacion->diagnostico              = $validated['diagnostico'];
        $planificacion->areas_id                 = $validated['areas_id'];
        $planificacion->persona_cargo_cursado_id = $finalCargoCursadoId; // Asigna el ID correcto del dueño
        $planificacion->tipo_planificacion       = $validated['tipo_planificacion'];
        $planificacion->save();

        \Illuminate\Support\Facades\DB::table('estados_anual')->insert([
            'estado' => 'Borrador',
            'fecha' => now()->toDateString(),
            'observaciones' => null,
            'planificacion_anual_id' => $planificacion->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'Mensaje' => 'Planificación anual creada correctamente',
            'data' => $planificacion
        ], 201);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json(['Mensaje' => 'Error de validación', 'Errors' => $e->errors()], 422);
    } catch (\Throwable $e) {
        return response()->json(['Mensaje' => 'Error al insertar planificación', 'Error' => $e->getMessage()], 500);
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
    try {
        $planificacion = PlanificacionAnual::find($id);

        if (!$planificacion) {
            return response()->json(['Mensaje' => 'Planificación no encontrada'], 404);
        }

        $request->validate([
            'fecha_presentacion'        => 'sometimes|date',
            'tipo_planificacion'        => 'sometimes|string|max:255',
            'areas_id'                  => 'sometimes|exists:areas,id',
            'persona_cargo_cursado_id'  => 'sometimes|integer',
        ]);

        if ($request->has('fecha_presentacion')) $planificacion->fecha_presentacion = $request->fecha_presentacion;
        if ($request->has('areas_id')) $planificacion->areas_id = $request->areas_id;
        if ($request->has('persona_cargo_cursado_id')) $planificacion->persona_cargo_cursado_id = $request->persona_cargo_cursado_id;
        if ($request->has('tipo_planificacion')) $planificacion->tipo_planificacion = $request->tipo_planificacion;

        $planificacion->saberes                = $request->input('saberes') ?? '';
        $planificacion->aprendizajes_esperados  = $request->input('aprendizajes_esperados') ?? '';
        $planificacion->criterios              = $request->input('criterios') ?? '';
        $planificacion->diagnostico            = $request->input('diagnostico') ?? '';
        $planificacion->bibliografia           = $request->input('bibliografia') ?? '';

        $planificacion->save();
        \Illuminate\Support\Facades\DB::table('estados_anual')->insert([
            'estado' => 'Borrador',
            'fecha' => now()->toDateString(),
            'observaciones' => null,
            'planificacion_anual_id' => $planificacion->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'Mensaje' => 'Planificación actualizada y regresada a estado borrador con éxito.',
            'data' => $planificacion
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json(['Mensaje' => 'Error de validación.', 'Errors' => $e->errors()], 422);
    } catch (\Exception $e) {
        return response()->json(['Mensaje' => 'Error crítico en Laravel.', 'Error' => $e->getMessage()], 500);
    }
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
