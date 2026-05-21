<?php

namespace App\Repositories\Eloquent;

use App\Models\Persona;
use App\Models\PlanificacionAnual;
use App\Models\PlanificacionDiaria;
use App\Repositories\Contracts\DirectorRepositoryInterface;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EloquentDirectorRepository extends BaseRepository implements DirectorRepositoryInterface
{
    /**
     * Cargos que en tu BD representan a un director.
     */
    private const CARGOS_DIRECTOR = [
        'Director 1° categoria',
        'Director 2° categoria',
        'Director 3° categoria',
    ];

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

    /**
     * Estado pendiente.
     */
    private const ESTADO_PENDIENTE = 'Pendiente';

    public function __construct(
        Persona $model,
        private PlanificacionAnual $planAnual,
        private PlanificacionDiaria $planDiaria
    ) {
        parent::__construct($model);
    }

    private function queryBase(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->model
            ->whereHas('personaCargos.cargo', function ($q) {
                $q->whereIn('cargo', self::CARGOS_DIRECTOR);
            })
            ->with([
                'personaCargos' => function ($q) {
                    $q->whereHas('cargo', function ($c) {
                        $c->whereIn('cargo', self::CARGOS_DIRECTOR);
                    })->with([
                        'cargo',
                        'sitRevista',
                    ]);
                },
            ]);
    }

    public function getAll(): EloquentCollection
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
        $persona = $this->model->findOrFail($id);
        $persona->update($data);

        return $persona->fresh();
    }

    public function delete(int $id): bool
    {
        return (bool) $this->model->findOrFail($id)->delete();
    }

    public function paginate(int $perPage = 15, array $filtros = []): LengthAwarePaginator
    {
        return $this->queryBase()->paginate($perPage);
    }

    public function getDirectorActivo(): ?object
    {
        return $this->queryBase()->first();
    }

    public function getDocentesBajoSupervision(): EloquentCollection
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
            ])
            ->get();
    }

    public function getPlanificacionesPendientes(string $tipo = 'todas'): Collection
    {
        $relacionesAnual = [
            'area',
            'estados' => fn($q) => $q->latest('fecha'),
            'personaCargoCursado.personaCargo.persona',
            'personaCargoCursado.cursado.curso',
        ];

        $relacionesDiaria = [
            'estados' => fn($q) => $q->latest('fecha'),
            'personaCargoCursado.personaCargo.persona',
            'personaCargoCursado.cursado.curso',
        ];

        if ($tipo === 'anual') {
            return $this->planAnual
                ->estadoActual(self::ESTADO_PENDIENTE)
                ->with($relacionesAnual)
                ->get()
                ->map(function ($item) {
                    $item->tipo_documento = 'anual';
                    return $item;
                })
                ->values();
        }

        if ($tipo === 'diaria') {
            return $this->planDiaria
                ->estadoActual(self::ESTADO_PENDIENTE)
                ->with($relacionesDiaria)
                ->get()
                ->map(function ($item) {
                    $item->tipo_documento = 'diaria';
                    return $item;
                })
                ->values();
        }

        $anuales = $this->planAnual
            ->estadoActual(self::ESTADO_PENDIENTE)
            ->with($relacionesAnual)
            ->get()
            ->map(function ($item) {
                $item->tipo_documento = 'anual';
                return $item;
            });

        $diarias = $this->planDiaria
            ->estadoActual(self::ESTADO_PENDIENTE)
            ->with($relacionesDiaria)
            ->get()
            ->map(function ($item) {
                $item->tipo_documento = 'diaria';
                return $item;
            });

        return $anuales
            ->concat($diarias)
            ->sortByDesc(function ($item) {
                return optional($item->estados->first())->fecha;
            })
            ->values();
    }

    public function getResumenEstados(): array
    {
        $anuales = DB::table('estados_anual as ea')
            ->select('ea.estado', DB::raw('COUNT(*) as total'))
            ->whereRaw('ea.fecha = (
                SELECT MAX(ea2.fecha)
                FROM estados_anual ea2
                WHERE ea2.planificacion_anual_id = ea.planificacion_anual_id
            )')
            ->groupBy('ea.estado')
            ->get()
            ->keyBy('estado')
            ->map(fn($r) => $r->total)
            ->toArray();

        $diarias = DB::table('estados_diaria as ed')
            ->select('ed.estado', DB::raw('COUNT(*) as total'))
            ->whereRaw('ed.fecha = (
                SELECT MAX(ed2.fecha)
                FROM estados_diaria ed2
                WHERE ed2.planificacion_diaria_id = ed.planificacion_diaria_id
            )')
            ->groupBy('ed.estado')
            ->get()
            ->keyBy('estado')
            ->map(fn($r) => $r->total)
            ->toArray();

        return [
            'planificaciones_anuales' => $anuales,
            'planificaciones_diarias' => $diarias,
        ];
    }
}
