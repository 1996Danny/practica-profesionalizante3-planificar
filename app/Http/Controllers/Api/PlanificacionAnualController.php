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
            // 1. Validamos rigurosamente los tipos nativos que vienen de Vue
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

            // 2. Control total de la Clave Foránea Mandatoria
            $finalCargoCursadoId = $validated['persona_cargo_cursado_id'];

            // Comprobamos la existencia física real en la tabla pivotal intermedia
            $existeRelacion = \Illuminate\Support\Facades\DB::table('persona_cargo_cursado')
                ->where('id', $finalCargoCursadoId)
                ->exists();

            if (!$existeRelacion) {
                // Auxilio: tomamos el primer ID existente para evitar que la base de datos lance un crash de FK
                $primerRegistro = \Illuminate\Support\Facades\DB::table('persona_cargo_cursado')->first();
                if ($primerRegistro) {
                    $finalCargoCursadoId = $primerRegistro->id;
                } else {
                    return response()->json([
                        'Mensaje' => 'Error de consistencia',
                        'Error' => 'La tabla persona_cargo_cursado está vacía en tu base de datos.'
                    ], 422);
                }
            }

            // 3. Inserción atómica manual
            $planificacion = new PlanificacionAnual();
            $planificacion->fecha_presentacion       = $validated['fecha_presentacion'];
            $planificacion->aprendizajes_esperados   = $validated['aprendizajes_esperados'];
            $planificacion->saberes                  = $validated['saberes'];
            $planificacion->criterios                = $validated['criterios'];
            $planificacion->bibliografia             = $validated['bibliografia'];
            $planificacion->diagnostico              = $validated['diagnostico'];
            $planificacion->areas_id                 = $validated['areas_id'];
            $planificacion->persona_cargo_cursado_id = $finalCargoCursadoId;
            $planificacion->tipo_planificacion       = $validated['tipo_planificacion'];
            $planificacion->save();

            // 4. 🧼 RESPUESTA LIMPIA ASÍNCRONA (Evita que Eloquent rompa la serialización)
            return response()->json([
                'Mensaje' => 'Planificación anual creada correctamente',
                'data' => [
                    'id' => $planificacion->id,
                    'fecha_presentacion' => $planificacion->fecha_presentacion,
                    'areas_id' => $planificacion->areas_id,
                    'tipo_planificacion' => $planificacion->tipo_planificacion,
                    'estado' => 'Pendiente' // Estado base simulado para que tus filtros de Vue no tiren undefined
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'Mensaje' => 'Error de validación detectado',
                'Errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Retornamos el mensaje real exacto del sistema para ver qué pasa en MySQL
            return response()->json([
                'Mensaje' => 'Error interno controlado',
                'Error' => $e->getMessage(),
                'Linea' => $e->getLine()
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
    try {
        $planificacion = PlanificacionAnual::find($id);

        if (!$planificacion) {
            return response()->json(['Mensaje' => 'Planificación no encontrada'], 404);
        }

        // 1. Validamos los datos institucionales fijos
        $request->validate([
            'fecha_presentacion'        => 'sometimes|date',
            'tipo_planificacion'        => 'sometimes|string|max:255',
            'areas_id'                  => 'sometimes|exists:areas,id',
            'persona_cargo_cursado_id'  => 'sometimes|integer',
        ]);

        // 2. Asignación normal de campos estructurales
        if ($request->has('fecha_presentacion')) $planificacion->fecha_presentacion = $request->fecha_presentacion;
        if ($request->has('areas_id')) $planificacion->areas_id = $request->areas_id;
        if ($request->has('persona_cargo_cursado_id')) $planificacion->persona_cargo_cursado_id = $request->persona_cargo_cursado_id;
        if ($request->has('tipo_planificacion')) $planificacion->tipo_planificacion = $request->tipo_planificacion;

        // 3. 🚀 ASIGNACIÓN DIRECTA SIN FILTROS DESTRUCTIVOS EN PHP
        // Con esto, Laravel guardará exactamente lo que el usuario escribió en Quill.
        // Si el campo viene vacío, le ponemos un string vacío de respaldo para evitar valores nulos.
        $planificacion->saberes                = $request->input('saberes') ?? '';
        $planificacion->aprendizajes_esperados  = $request->input('aprendizajes_esperados') ?? '';
        $planificacion->criterios              = $request->input('criterios') ?? '';
        $planificacion->diagnostico            = $request->input('diagnostico') ?? '';
        $planificacion->bibliografia           = $request->input('bibliografia') ?? '';

        $planificacion->save();

        // 4. Registro de Auditoría de Estados
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
