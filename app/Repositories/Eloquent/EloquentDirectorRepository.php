<?php

namespace App\Repositories\Eloquent;

use App\Models\Persona;
use App\Models\PlanificacionAnual;
use App\Models\PlanificacionDiaria;
use App\Repositories\Contracts\DirectorRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EloquentDirectorRepository extends BaseRepository implements DirectorRepositoryInterface
{
    private const CARGO_DIRECTOR = 'Director';
    private const CARGO_DOCENTE  = 'Docente';
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
                $q->where('cargo', self::CARGO_DIRECTOR);
            })
            ->with([
                'personaCargos' => function ($q) {
                    $q->whereHas('cargo', fn($c) => $c->where('cargo', self::CARGO_DIRECTOR))
                        ->with(['cargo', 'sitRevista']);
                },
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

    /**
     * Obtener el primer director activo.
     */
    public function getDirectorActivo(): ?object
    {
        return $this->queryBase()->first();
    }

    /**
     * Obtener todos los docentes del sistema para supervisión.
     */
    public function getDocentesBajoSupervision(): Collection
    {
        return $this->model
            ->whereHas('personaCargos.cargo', fn($q) => $q->where('cargo', self::CARGO_DOCENTE))
            ->with([
                'personaCargos' => function ($q) {
                    $q->whereHas('cargo', fn($c) => $c->where('cargo', self::CARGO_DOCENTE))
                        ->with([
                            'cargo',
                            'sitRevista',
                            'personaCargoCursados.cursado.curso',
                        ]);
                },
            ])
            ->get();
    }

    /**
     * Obtener planificaciones pendientes filtradas por tipo.
     */
    public function getPlanificacionesPendientes(string $tipo = 'todas'): Collection
    {
        $relacionesAnual = [
            'area',
            'estados'                              => fn($q) => $q->latest('fecha'),
            'personaCargoCursado.personaCargo.persona',
            'personaCargoCursado.cursado.curso',
        ];

        $relacionesDiaria = [
            'estados'                              => fn($q) => $q->latest('fecha'),
            'personaCargoCursado.personaCargo.persona',
            'personaCargoCursado.cursado.curso',
        ];

        if ($tipo === 'anual') {
            return $this->planAnual
                ->estadoActual(self::ESTADO_PENDIENTE)
                ->with($relacionesAnual)
                ->get();
        }

        if ($tipo === 'diaria') {
            return $this->planDiaria
                ->estadoActual(self::ESTADO_PENDIENTE)
                ->with($relacionesDiaria)
                ->get();
        }

        // 'todas': unimos ambas colecciones
        $anuales = $this->planAnual
            ->estadoActual(self::ESTADO_PENDIENTE)
            ->with($relacionesAnual)
            ->get()
            ->each(fn($p) => $p->tipo_documento = 'anual');

        $diarias = $this->planDiaria
            ->estadoActual(self::ESTADO_PENDIENTE)
            ->with($relacionesDiaria)
            ->get()
            ->each(fn($p) => $p->tipo_documento = 'diaria');

        // Merge de colecciones Eloquent
        return $anuales->merge($diarias);
    }

    /**
     * Resumen estadístico: conteo de planificaciones por estado.
     */
    public function getResumenEstados(): array
    {
        $anuales = DB::table('estados_anual as ea')
            ->select('ea.estado', DB::raw('COUNT(*) as total'))
            ->whereRaw('ea.fecha = (
                SELECT MAX(ea2.fecha) FROM estados_anual ea2
                WHERE ea2.planificacion_anual_id = ea.planificacion_anual_id
            )')
            ->groupBy('ea.estado')
            ->get()
            ->keyBy('estado')
            ->map(fn($r) => $r->total);

        $diarias = DB::table('estados_diaria as ed')
            ->select('ed.estado', DB::raw('COUNT(*) as total'))
            ->whereRaw('ed.fecha = (
                SELECT MAX(ed2.fecha) FROM estados_diaria ed2
                WHERE ed2.planificacion_diaria_id = ed.planificacion_diaria_id
            )')
            ->groupBy('ed.estado')
            ->get()
            ->keyBy('estado')
            ->map(fn($r) => $r->total);

        return [
            'planificaciones_anuales'  => $anuales,
            'planificaciones_diarias'  => $diarias,
        ];
    }
}
