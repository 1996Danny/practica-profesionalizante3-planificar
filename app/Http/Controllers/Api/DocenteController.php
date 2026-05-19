<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DocenteResource;
use App\Repositories\Contracts\DocenteRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DocenteController extends Controller
{
    public function __construct(
        // Laravel resuelve automáticamente la implementación
        // gracias al binding en RepositoryServiceProvider
        private readonly DocenteRepositoryInterface $docenteRepo
    ) {}

    /**
     * GET /api/docentes
     * GET /api/docentes?nombre=Garcia
     * GET /api/docentes?anio_lectivo=2025
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filtros = $request->only(['nombre', 'anio_lectivo']);

        $docentes = empty($filtros)
            ? $this->docenteRepo->getAll()
            : $this->docenteRepo->paginate(15, $filtros);

        return DocenteResource::collection($docentes);
    }

    /**
     * GET /api/docentes/{id}
     */
    public function show(int $id): DocenteResource|JsonResponse
    {
        $docente = $this->docenteRepo->findById($id);

        if (!$docente) {
            return response()->json([
                'message' => "Docente con ID {$id} no encontrado.",
            ], 404);
        }

        return new DocenteResource($docente);
    }

    /**
     * GET /api/docentes/{id}/cursados
     */
    public function cursados(int $id): JsonResponse
    {
        $docente = $this->docenteRepo->findById($id);

        if (!$docente) {
            return response()->json(['message' => 'Docente no encontrado.'], 404);
        }

        $cursados = $this->docenteRepo->getCursadosByDocente($id);

        return response()->json([
            'data'    => $cursados,
            'total'   => $cursados->count(),
            'message' => 'Cursados del docente obtenidos correctamente.',
        ]);
    }

    /**
     * GET /api/docentes/buscar?q=termino
     */
    public function buscar(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $request->validate(['q' => 'required|string|min:2']);

        $docentes = $this->docenteRepo->buscarPorNombre($request->q);

        return DocenteResource::collection($docentes);
    }
}
