<?php

namespace App\Repositories\Eloquent;

use App\Models\PlanificacionAnual;
use App\Repositories\Contracts\PlanificacionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentPlanificacionAnualRepository extends BaseRepository
implements PlanificacionRepositoryInterface
{
    public function __construct(PlanificacionAnual $model)
    {
        parent::__construct($model);
    }

    public function getTipo(): string
    {
        return 'anual';
    }

    /**
     * Query base con todas las relaciones necesarias.
     */
    private function queryBase(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->model->with([
            'area',
            // El estado más reciente primero
            'estados'                                    => fn($q) => $q->latest('fecha'),
            'personaCargoCursado.personaCargo.persona',
            'personaCargoCursado.personaCargo.cargo',
            'personaCargoCursado.personaCargo.sitRevista',
            'personaCargoCursado.cursado.curso',
        ]);
    }

    public function getAll(): Collection
    {
        return $this->queryBase()->get();
    }

    public function findById(int $id): ?object
    {
        return $this->queryBase()->find($id);
    }

    public function create(array $data): object
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): object
    {
        $plan = $this->model->findOrFail($id);
        $plan->update($data);
        return $plan->fresh();
    }

    public function delete(int $id): bool
    {
        return (bool) $this->model->findOrFail($id)->delete();
    }

    /**
     * Paginar con filtros dinámicos.
     * Filtros: docente_id, estado, area_id, tipo_planificacion, anio_lectivo.
     */
    public function paginate(int $perPage = 15, array $filtros = []): LengthAwarePaginator
    {
        $query = $this->queryBase();
        $this->aplicarFiltros($query, $filtros);
        return $query->latest()->paginate($perPage);
    }

    /**
     * Centraliza la aplicación de filtros.
     * Se usa tanto en paginate() como en otros métodos.
     */
    private function aplicarFiltros(
        \Illuminate\Database\Eloquent\Builder $query,
        array $filtros
    ): void {
        if (!empty($filtros['docente_id'])) {
            $query->whereHas('personaCargoCursado.personaCargo', function ($q) use ($filtros) {
                $q->where('personas_id', $filtros['docente_id']);
            });
        }

        if (!empty($filtros['estado'])) {
            $query->estadoActual($filtros['estado']);
        }

        if (!empty($filtros['area_id'])) {
            $query->where('areas_id', $filtros['area_id']);
        }

        if (!empty($filtros['tipo_planificacion'])) {
            $query->where('tipo_planificacion', $filtros['tipo_planificacion']);
        }

        if (!empty($filtros['anio_lectivo'])) {
            $query->whereHas('personaCargoCursado.cursado', function ($q) use ($filtros) {
                $q->where('anio_lectivo', $filtros['anio_lectivo']);
            });
        }
    }

    public function getByDocente(int $personaId): Collection
    {
        return $this->queryBase()
            ->whereHas('personaCargoCursado.personaCargo', function ($q) use ($personaId) {
                $q->where('personas_id', $personaId);
            })
            ->get();
    }

    public function getByEstado(string $estado): Collection
    {
        return $this->queryBase()
            ->estadoActual($estado)
            ->get();
    }

    public function getByDocenteYEstado(int $personaId, string $estado): Collection
    {
        return $this->queryBase()
            ->whereHas('personaCargoCursado.personaCargo', function ($q) use ($personaId) {
                $q->where('personas_id', $personaId);
            })
            ->estadoActual($estado)
            ->get();
    }

    public function getByAnioLectivo(string $anioLectivo): Collection
    {
        return $this->queryBase()
            ->whereHas('personaCargoCursado.cursado', function ($q) use ($anioLectivo) {
                $q->where('anio_lectivo', $anioLectivo);
            })
            ->get();
    }

    public function getByAsignacion(int $personaCargoCursadoId): Collection
    {
        return $this->queryBase()
            ->where('persona_cargo_cursado_id', $personaCargoCursadoId)
            ->get();
    }
}
