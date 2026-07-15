<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlanificacionAnual;
use App\Models\PlanificacionDiaria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanificacionController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $anualesQuery = PlanificacionAnual::with([
            'area',
            'personaCargoCursado.personaCargo.persona',
            'personaCargoCursado.cursado.curso'
        ]);

        $diariasQuery = PlanificacionDiaria::with([
            'personaCargoCursado.personaCargo.persona',
            'personaCargoCursado.cursado.curso'
        ]);

        if ($user->role === 'docente') {

            $anualesQuery->whereHas('personaCargoCursado.personaCargo', function ($query) use ($user) {
                $query->where('personas_id', $user->persona_id);
            });

            $diariasQuery->whereHas('personaCargoCursado.personaCargo', function ($query) use ($user) {
                $query->where('personas_id', $user->persona_id);
            });

        } elseif ($user->role === 'director') {

            $anualesQuery->whereHas('estados', function ($query) {
                $query->where('estado', '!=', 'Borrador');
            });

            $diariasQuery->whereHas('estados', function ($query) {
                $query->where('estado', '!=', 'Borrador');
            });
        }

        $anuales = $anualesQuery->get();
        $diarias = $diariasQuery->get();

        return response()->json([
            'anuales' => $anuales,
            'diarias' => $diarias
        ], 200);
    }

    public function show(int $id): JsonResponse
    {
        $plan = PlanificacionAnual::with([
            'area',
            'personaCargoCursado.personaCargo.persona',
            'personaCargoCursado.cursado.curso',
            'estados'
        ])->find($id);

        if (!$plan) {
            $plan = PlanificacionDiaria::with([
                'personaCargoCursado.personaCargo.persona',
                'personaCargoCursado.cursado.curso',
                'estados'
            ])->find($id);
        }

        if (!$plan) {
            return response()->json([
                'message' => 'Planificación no encontrada'
            ], 404);
        }

        $responseData = $plan->toArray();

        $responseData['saberes'] = $plan->saberes ?? '';
        $responseData['criterios'] = $plan->criterios ?? '';
        $responseData['diagnostico'] = $plan->diagnostico ?? '';
        $responseData['aprendizajes_esperados'] = $plan->aprendizajes_esperados ?? '';
        $responseData['bibliografia'] = $plan->bibliografia ?? '';

        return response()->json($responseData, 200);
    }

public function update(Request $request, $id)
{
    try {
        $plan = PlanificacionAnual::findOrFail($id);

        if ($request->has('fecha_presentacion')) $plan->fecha_presentacion = $request->fecha_presentacion;
        if ($request->has('areas_id') && !empty($request->areas_id)) $plan->areas_id = $request->areas_id;
        if ($request->has('persona_cargo_cursado_id') && !empty($request->persona_cargo_cursado_id)) {
            $plan->persona_cargo_cursado_id = $request->persona_cargo_cursado_id;
        }
        if ($request->has('tipo_planificacion')) $plan->tipo_planificacion = $request->tipo_planificacion;

        $plan->saberes = $request->input('saberes', '');
        $plan->aprendizajes_esperados = $request->input('aprendizajes_esperados', '');
        $plan->criterios = $request->input('criterios', '');
        $plan->diagnostico = $request->input('diagnostico', '');
        $plan->bibliografia = $request->input('bibliografia', '');

        $plan->save();

        \Illuminate\Support\Facades\DB::table('estados_anual')->insert([
            'estado' => 'Borrador',
            'fecha' => now()->toDateString(),
            'observaciones' => null,
            'planificacion_anual_id' => $plan->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $plan->load(['area', 'personaCargoCursado.personaCargo.persona', 'personaCargoCursado.cursado.curso', 'estados']);

        return response()->json([
            'Mensaje' => 'Planificación actualizada correctamente',
            'data' => $plan
        ], 200);

    } catch (\Exception $e) {
        return response()->json(['error' => 'No se pudo actualizar: ' . $e->getMessage()], 500);
    }
}
}
