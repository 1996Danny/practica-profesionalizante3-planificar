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

        // 🟢 FORZAMOS LA VERIFICACIÓN DE ATRIBUTOS ANTES DE ENVIAR EL JSON
        // Si los nombres en tu base de datos varían, Eloquent los mapeará aquí de forma segura:
        $responseData = $plan->toArray();

        // Aseguramos que existan las llaves en el JSON que va hacia Vue
        $responseData['saberes'] = $plan->saberes ?? '';
        $responseData['criterios'] = $plan->criterios ?? '';
        $responseData['diagnostico'] = $plan->diagnostico ?? '';
        $responseData['aprendizajes_esperados'] = $plan->aprendizajes_esperados ?? '';
        $responseData['bibliografia'] = $plan->bibliografia ?? '';

        return response()->json($responseData, 200);
    }

    /**
     * NUEVO: Actualizar una planificación desde el dashboard unificado
     */
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

        // 🌟 Forzamos la actualización manual de los contenidos enriquecidos
        $plan->saberes = $request->input('saberes', '');
        $plan->aprendizajes_esperados = $request->input('aprendizajes_esperados', '');
        $plan->criterios = $request->input('criterios', '');
        $plan->diagnostico = $request->input('diagnostico', '');
        $plan->bibliografia = $request->input('bibliografia', '');

        $plan->save();

        // 🔄 Historial de Estados
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
