<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class EstadoAnualController extends Controller
{
    /**
     * El Docente envía la planificación al director
     */
    public function enviarRevision(Request $request, $id): JsonResponse
    {
        try {
            // Insertamos el nuevo estado en la tabla estados_anual
            DB::table('estados_anual')->insert([
                'estado' => 'Enviada',
                'fecha' => now()->toDateString(),
                'planificacion_anual_id' => $id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['Mensaje' => 'Planificación enviada al Director con éxito.'], 200);
        } catch (\Exception $e) {
            return response()->json(['Mensaje' => 'Error al enviar', 'Error' => $e->getMessage()], 500);
        }
    }

    /**
     * El Director Aprueba la planificación
     */
    public function aprobar(Request $request, $id): JsonResponse
    {
        try {
            DB::table('estados_anual')->insert([
                'estado' => 'Aprobada',
                'fecha' => now()->toDateString(),
                'planificacion_anual_id' => $id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['Mensaje' => 'Planificación aprobada con éxito.'], 200);
        } catch (\Exception $e) {
            return response()->json(['Mensaje' => 'Error al aprobar', 'Error' => $e->getMessage()], 500);
        }
    }

    /**
     * El Director Rechaza u Observa la planificación
     */
    public function rechazar(Request $request, $id): JsonResponse
    {
        try {
            DB::table('estados_anual')->insert([
                'estado' => 'Rechazada',
                'fecha' => now()->toDateString(),
                'planificacion_anual_id' => $id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['Mensaje' => 'Planificación rechazada/observada.'], 200);
        } catch (\Exception $e) {
            return response()->json(['Mensaje' => 'Error al procesar rechazo', 'Error' => $e->getMessage()], 500);
        }
    }
}
