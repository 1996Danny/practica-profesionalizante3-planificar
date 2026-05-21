<?php

namespace App\Repositories\Eloquent;

use App\Models\Persona;
use App\Models\PersonaCargoCursado;
use App\Repositories\Contracts\DocenteRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentDocenteRepository extends BaseRepository implements DocenteRepositoryInterface
{
    /**
     * Cargos que en tu BD representan a un docente.
     */
    private const CARGOS_DOCENTES = [
        'Maestro',
        'Maestro Especial Música',
        'Maestro Especial ED. Física',
        'Maestro Especial Plástica',
        'Maestro Especial Tecnología',
    ];

    public function __construct(
        Persona $model,
        private PersonaCargoCursado $asignacion
    ) {
        parent::__construct($model);
    }

    /**
     * Query base reutilizable para docentes.
     */
    private function queryBase(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->model
            ->whereHas('personaCargos.cargo', function ($q) {
                $q->whereIn('cargo', self::CARGOS_DOCENTES);
            })
            ->with([
                'personaCargos' => function ($q) {
                    $q->whereHas('cargo', function ($c) {
                        $c->whereIn('cargo', self::CARGOS_DOCENTES);
                    })->with([
                        'cargo',
                        'sitRevista',
                        'personaCargoCursados.cursado.curso',
                    ]);
                },
            ]);
    }

    /**
     * Listar todos los docentes.
     */
    public function getAll(): Collection
    {
        return $this->queryBase()->get();
    }

    /**
     * Obtener un docente por ID con relaciones completas.
     */
    public function findById(int $id): ?object
    {
        return $this->model
            ->whereHas('personaCargos.cargo', function ($q) {
                $q->whereIn('cargo', self::CARGOS_DOCENTES);
            })
            ->with([
                'personaCargos' => function ($q) {
                    $q->whereHas('cargo', function ($c) {
                        $c->whereIn('cargo', self::CARGOS_DOCENTES);
                    })->with([
                        'cargo',
                        'sitRevista',
                        'personaCargoCursados.cursado.curso',
                        'personaCargoCursados.planificacionesAnuales.estados',
                        'personaCargoCursados.planificacionesDiarias.estados',
                    ]);
                },
            ])
            ->find($id);
    }

    /**
     * Crear una nueva persona docente.
     * Nota: este método crea solo la persona, no persona_cargos.
     */
    public function create(array $data): object
    {
        return $this->model->create($data);
    }

    /**
     * Actualizar datos de la persona docente.
     */
    public function update(int $id, array $data): object
    {
        $persona = $this->model->findOrFail($id);
        $persona->update($data);

        return $persona->fresh();
    }

    /**
     * Eliminar (soft delete) un docente.
     */
    public function delete(int $id): bool
    {
        $persona = $this->model->findOrFail($id);

        return (bool) $persona->delete();
    }

    /**
     * Paginar docentes con filtros opcionales.
     */
    public function paginate(int $perPage = 15, array $filtros = []): LengthAwarePaginator
    {
        $query = $this->queryBase();

        if (!empty($filtros['nombre'])) {
            $termino = $filtros['nombre'];

            $query->where(function ($q) use ($termino) {
                $q->where('apellidos', 'LIKE', "%{$termino}%")
                    ->orWhere('nombres', 'LIKE', "%{$termino}%");
            });
        }

        if (!empty($filtros['anio_lectivo'])) {
            $query->whereHas(
                'personaCargos.personaCargoCursados.cursado',
                function ($q) use ($filtros) {
                    $q->where('anio_lectivo', $filtros['anio_lectivo']);
                }
            );
        }

        return $query->paginate($perPage);
    }

    /**
     * Obtener cursados asignados a un docente.
     */
    public function getCursadosByDocente(int $personaId): Collection
    {
        return $this->asignacion
            ->whereHas('personaCargo', function ($q) use ($personaId) {
                $q->where('personas_id', $personaId)
                    ->whereHas('cargo', function ($c) {
                        $c->whereIn('cargo', self::CARGOS_DOCENTES);
                    });
            })
            ->with([
                'cursado.curso',
                'personaCargo.cargo',
                'personaCargo.sitRevista',
                'planificacionesAnuales.estados',
                'planificacionesDiarias.estados',
            ])
            ->get();
    }

    /**
     * Obtener docentes asignados a un cursado específico.
     */
    public function getDocentesByCursado(int $cursadoId): Collection
    {
        return $this->model
            ->whereHas('personaCargos.cargo', function ($q) {
                $q->whereIn('cargo', self::CARGOS_DOCENTES);
            })
            ->whereHas('personaCargos.personaCargoCursados', function ($q) use ($cursadoId) {
                $q->where('cursados_id', $cursadoId);
            })
            ->with([
                'personaCargos' => function ($q) use ($cursadoId) {
                    $q->whereHas('cargo', function ($c) {
                        $c->whereIn('cargo', self::CARGOS_DOCENTES);
                    })->with([
                        'cargo',
                        'sitRevista',
                        'personaCargoCursados' => function ($a) use ($cursadoId) {
                            $a->where('cursados_id', $cursadoId)
                                ->with('cursado.curso');
                        },
                    ]);
                },
            ])
            ->get();
    }

    /**
     * Obtener docentes activos en un año lectivo.
     */
    public function getByAnioLectivo(string $anioLectivo): Collection
    {
        return $this->model
            ->whereHas('personaCargos.cargo', function ($q) {
                $q->whereIn('cargo', self::CARGOS_DOCENTES);
            })
            ->whereHas(
                'personaCargos.personaCargoCursados.cursado',
                function ($q) use ($anioLectivo) {
                    $q->where('anio_lectivo', $anioLectivo);
                }
            )
            ->with([
                'personaCargos' => function ($q) use ($anioLectivo) {
                    $q->whereHas('cargo', function ($c) {
                        $c->whereIn('cargo', self::CARGOS_DOCENTES);
                    })->with([
                        'cargo',
                        'sitRevista',
                        'personaCargoCursados' => function ($a) use ($anioLectivo) {
                            $a->whereHas('cursado', function ($c) use ($anioLectivo) {
                                $c->where('anio_lectivo', $anioLectivo);
                            })->with('cursado.curso');
                        },
                    ]);
                },
            ])
            ->get();
    }

    /**
     * Buscar docentes por nombre o apellido.
     */
    public function buscarPorNombre(string $termino): Collection
    {
        return $this->queryBase()
            ->where(function ($q) use ($termino) {
                $q->where('apellidos', 'LIKE', "%{$termino}%")
                    ->orWhere('nombres', 'LIKE', "%{$termino}%");
            })
            ->get();
    }

    /**
     * Verificar si un docente tiene asignaciones activas.
     */
    public function tieneAsignaciones(int $personaId): bool
    {
        return $this->asignacion
            ->whereHas('personaCargo', function ($q) use ($personaId) {
                $q->where('personas_id', $personaId)
                    ->whereHas('cargo', function ($c) {
                        $c->whereIn('cargo', self::CARGOS_DOCENTES);
                    });
            })
            ->exists();
    }
}
