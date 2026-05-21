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
     * Valor exacto del campo 'cargo' en la tabla 'cargos'.
     * ⚠️ Ajusta si en tu BD el valor es diferente.
     */
    private const CARGO = 'Docente';

    public function __construct(
        Persona $model,
        private PersonaCargoCursado $asignacion
    ) {
        parent::__construct($model);
    }

    // ─────────────────────────────────────────────────
    // MÉTODO PRIVADO: Eager loading estándar
    // Centraliza las relaciones para no repetirlas
    // ─────────────────────────────────────────────────

    private function queryBase(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->model
            // Filtramos SOLO personas con cargo 'Docente'
            ->whereHas('personaCargos.cargo', function ($q) {
                $q->where('cargo', self::CARGO);
            })
            ->with([
                'personaCargos' => function ($q) {
                    // Cargamos solo el cargo Docente, no otros cargos
                    $q->whereHas('cargo', fn($c) => $c->where('cargo', self::CARGO))
                        ->with([
                            'cargo',
                            'sitRevista',
                            'personaCargoCursados.cursado.curso',
                        ]);
                },
            ]);
    }

    // ─────────────────────────────────────────────────
    // IMPLEMENTACIÓN DE LA INTERFAZ
    // ─────────────────────────────────────────────────

    /**
     * Listar todos los docentes.
     */
    public function getAll(): Collection
    {
        return $this->queryBase()->get();
    }

    /**
     * Obtener un docente por su persona_id con planificaciones incluidas.
     */
    public function findById(int $id): ?object
    {
        return $this->model
            ->whereHas('personaCargos.cargo', fn($q) => $q->where('cargo', self::CARGO))
            ->with([
                'personaCargos' => function ($q) {
                    $q->whereHas('cargo', fn($c) => $c->where('cargo', self::CARGO))
                        ->with([
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
     * Crear un docente (crea la persona + persona_cargo).
     * $data debe incluir: datos de persona + cargos_id + sit_revista_id.
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

        // Filtro opcional por nombre/apellido
        if (!empty($filtros['nombre'])) {
            $termino = $filtros['nombre'];
            $query->where(function ($q) use ($termino) {
                $q->where('apellidos', 'LIKE', "%{$termino}%")
                    ->orWhere('nombres', 'LIKE', "%{$termino}%");
            });
        }

        // Filtro por año lectivo
        if (!empty($filtros['anio_lectivo'])) {
            $query->whereHas(
                'personaCargos.personaCargoCursados.cursado',
                fn($q) => $q->where('anio_lectivo', $filtros['anio_lectivo'])
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
                    ->whereHas('cargo', fn($c) => $c->where('cargo', self::CARGO));
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
     * Obtener docentes asignados a un cursado.
     */
    public function getDocentesByCursado(int $cursadoId): Collection
    {
        return $this->model
            ->whereHas('personaCargos.cargo', fn($q) => $q->where('cargo', self::CARGO))
            ->whereHas('personaCargos.personaCargoCursados', function ($q) use ($cursadoId) {
                $q->where('cursados_id', $cursadoId);
            })
            ->with([
                'personaCargos' => function ($q) use ($cursadoId) {
                    $q->with([
                        'cargo',
                        'personaCargoCursados' => fn($a) => $a->where('cursados_id', $cursadoId)
                            ->with('cursado.curso'),
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
            ->whereHas('personaCargos.cargo', fn($q) => $q->where('cargo', self::CARGO))
            ->whereHas(
                'personaCargos.personaCargoCursados.cursado',
                fn($q) => $q->where('anio_lectivo', $anioLectivo)
            )
            ->with([
                'personaCargos' => function ($q) use ($anioLectivo) {
                    $q->with([
                        'cargo',
                        'sitRevista',
                        'personaCargoCursados' => function ($a) use ($anioLectivo) {
                            $a->whereHas('cursado', fn($c) => $c->where('anio_lectivo', $anioLectivo))
                                ->with('cursado.curso');
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
                    ->whereHas('cargo', fn($c) => $c->where('cargo', self::CARGO));
            })
            ->exists();
    }
}
