<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlanificacionAnualResource;
use App\Repositories\Contracts\PlanificacionRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PlanificacionAnualController extends Controller
{
    public function __construct(
        // Resuelve 'planificacion.anual' del Service Provider
        private readonly PlanificacionRepositoryInterface $repo
    ) {}

    /**
     * GET /api/planificaciones/anuales
     * Parámetros opcionales:
     *   ?docente_id=1
     *   ?estado=Pendiente
     *   ?area_id=2
     *   ?anio_lectivo=2025
     *   ?tipo_planificacion=mensual
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filtros = $request->only([
            'docente_id',
            'estado',
            'area_id',
            'anio_lectivo',
            'tipo_planificacion',
        ]);

        $planificaciones = $this->repo->paginate(15, $filtros);

        return PlanificacionAnualResource::collection($planificaciones);
    }

    /**
     * GET /api/planificaciones/anuales/{id}
     */
    public function show(int $id): PlanificacionAnualResource|JsonResponse
    {
        $plan = $this->repo->findById($id);

        if (!$plan) {
            return response()->json(['message' => 'Planificación no encontrada.'], 404);
        }

        return new PlanificacionAnualResource($plan);
    }

    /**
     * GET /api/planificaciones/anuales/docente/{personaId}
     */
    public function porDocente(int $personaId): AnonymousResourceCollection
    {
        return PlanificacionAnualResource::collection(
            $this->repo->getByDocente($personaId)
        );
    }

    /**
     * GET /api/planificaciones/anuales/estado/{estado}
     * Ej: /api/planificaciones/anuales/estado/Pendiente
     */
    public function porEstado(string $estado): AnonymousResourceCollection
    {
        return PlanificacionAnualResource::collection(
            $this->repo->getByEstado($estado)
        );
    }

    /**
     * POST /api/planificaciones/anuales
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fecha_presentacion'     => 'required|date',
            'aprendizajes_esperados' => 'required|string',
            'saberes'                => 'required|string',
            'criterios'              => 'required|string',
            'bibliografia'           => 'required|string',
            'diagnostico'            => 'required|string',
            'areas_id'               => 'required|exists:areas,id',
            'persona_cargo_cursado_id' => 'required|exists:persona_cargo_cursado,id',
            'tipo_planificacion'     => 'required|string|max:255',
        ]);

        $plan = $this->repo->create($validated);

        return response()->json([
            'message' => 'Planificación anual creada correctamente.',
            'data'    => new PlanificacionAnualResource($this->repo->findById($plan->id)),
        ], 201);
    }

    /**
     * PUT /api/planificaciones/anuales/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $plan = $this->repo->findById($id);

        if (!$plan) {
            return response()->json(['message' => 'Planificación no encontrada.'], 404);
        }

        $validated = $request->validate([
            'fecha_presentacion'     => 'sometimes|date',
            'aprendizajes_esperados' => 'sometimes|string',
            'saberes'                => 'sometimes|string',
            'criterios'              => 'sometimes|string',
            'bibliografia'           => 'sometimes|string',
            'diagnostico'            => 'sometimes|string',
            'areas_id'               => 'sometimes|exists:areas,id',
            'tipo_planificacion'     => 'sometimes|string|max:255',
        ]);

        $planActualizado = $this->repo->update($id, $validated);

        return response()->json([
            'message' => 'Planificación actualizada.',
            'data'    => new PlanificacionAnualResource($planActualizado),
        ]);
    }

    /**
     * DELETE /api/planificaciones/anuales/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        if (!$this->repo->findById($id)) {
            return response()->json(['message' => 'Planificación no encontrada.'], 404);
        }

        $this->repo->delete($id);

        return response()->json(['message' => 'Planificación eliminada correctamente.']);
    }
}
