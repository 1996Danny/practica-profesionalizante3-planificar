<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DirectorResource;
use App\Repositories\Contracts\DirectorRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DirectorController extends Controller
{
    public function __construct(
        private readonly DirectorRepositoryInterface $directorRepo
    ) {}

    /**
     * GET /api/directores
     */
    public function index(): JsonResponse
    {
        $directores = $this->directorRepo->getAll();

        return response()->json([
            'data'  => $directores,
            'total' => $directores->count(),
        ]);
    }

    /**
     * GET /api/directores/{id}
     */
    public function show(int $id): JsonResponse
    {
        $director = $this->directorRepo->findById($id);

        if (!$director) {
            return response()->json(['message' => 'Director no encontrado.'], 404);
        }

        return response()->json(['data' => $director]);
    }

    /**
     * GET /api/directores/activo
     */
    public function activo(): JsonResponse
    {
        $director = $this->directorRepo->getDirectorActivo();

        if (!$director) {
            return response()->json(['message' => 'No hay director activo registrado.'], 404);
        }

        return response()->json(['data' => $director]);
    }

    /**
     * GET /api/directores/supervision/docentes
     */
    public function docentes(): JsonResponse
    {
        $docentes = $this->directorRepo->getDocentesBajoSupervision();

        return response()->json([
            'data'  => $docentes,
            'total' => $docentes->count(),
        ]);
    }

    /**
     * GET /api/directores/planificaciones/pendientes
     * GET /api/directores/planificaciones/pendientes?tipo=anual
     * GET /api/directores/planificaciones/pendientes?tipo=diaria
     */
    public function planificacionesPendientes(Request $request): JsonResponse
    {
        $tipo = $request->get('tipo', 'todas');

        $planificaciones = $this->directorRepo->getPlanificacionesPendientes($tipo);

        return response()->json([
            'tipo'  => $tipo,
            'data'  => $planificaciones,
            'total' => $planificaciones->count(),
        ]);
    }

    /**
     * GET /api/directores/estadisticas/estados
     */
    public function resumenEstados(): JsonResponse
    {
        return response()->json([
            'data' => $this->directorRepo->getResumenEstados(),
        ]);
    }
}
