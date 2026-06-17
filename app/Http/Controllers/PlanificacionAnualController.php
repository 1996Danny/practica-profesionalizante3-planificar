<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanificacionAnualController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $planificacion = DB::table('planificacion_anual')->get();
        return response()->json($planificacion);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {

            $planificacion = DB::table('planificacion_anual')->insert([
                'fecha_presentacion' => request('fecha_presentacion'),
                'aprendizajes_esperados' => request('aprendizajes_esperados'),
                'saberes' => request('saberes'),
                'criterios' => request('criterios'),
                'bibliografia' => request('bibliografia'),
                'diagnostico' => request('diagnostico'),
                'areas_id' => request('areas_id'),
                'persona_cargo_cursado_id' => request('persona_cargo_cursado_id'),
                'tipo_planificacion' => request('tipo_planificacion'),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json(['Mensaje' => 'Agregado Correctamente'], 201);
        } catch (\Throwable $e) {

            return response()->json([
                'Mensaje' => 'Error al insertar planificación',
                'Error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $planificacion = DB::table('planificacion_anual')
            ->where('id', $id)
            ->first();

        if (!$planificacion) {
            return response()->json([
                'message' => 'Planificación no encontrada'
            ], 404);
        }

        return response()->json($planificacion);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $planificacion = DB::table('planificacion_anual')->where('id', '=', $id)->update([
            'fecha_presentacion' => $request['fecha_presentacion'],
            'aprendizajes_esperados' => $request['aprendizajes_esperados'],
            'saberes' => $request['saberes'],
            'criterios' => $request['criterios'],
            'bibliografia' => $request['bibliografia'],
            'diagnostico' => $request['diagnostico'],
            'areas_id' => $request['areas_id'],
            'persona_cargo_cursado_id' => $request['persona_cargo_cursado_id'],
            'tipo_planificacion' => $request['tipo_planificacion'],
            'updated_at' => now(),
        ]);
        if (!$planificacion) {
            return response()->json([
                'Mensaje' => 'Planificaciones no Encontradas'
            ], 404);
        }
        return response()->json(['Mensaje' => 'Actualizado correctamente']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $planificacion = DB::table('planificacion_anual')->where('id', '=', $id)->delete();
        if (!$planificacion) {
            return response()->json([
                'mensaje' => 'Planificacion no eliminada'
            ], 404);
        }
        return response()->json([$planificacion, 'Mensaje' => 'Eliminado correctamente']);
    }
}
