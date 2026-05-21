<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanificacionAnual extends Model
{
    protected $table = 'planificacion_anual';

    protected $fillable = [
        'fecha_presentacion',
        'aprendizajes_esperados',
        'saberes',
        'criterios',
        'bibliografia',
        'diagnostico',
        'areas_id',
        'persona_cargo_cursado_id',
        'tipo_planificacion',
    ];

    protected $casts = [
        'fecha_presentacion' => 'date',
    ];

    // ─────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'areas_id');
    }

    public function personaCargoCursado(): BelongsTo
    {
        return $this->belongsTo(PersonaCargoCursado::class, 'persona_cargo_cursado_id');
    }

    public function estados(): HasMany
    {
        return $this->hasMany(EstadoAnual::class, 'planificacion_anual_id');
    }

    // ─────────────────────────────────────────
    // SCOPES (filtros reutilizables)
    // ─────────────────────────────────────────

    /**
     * Scope para filtrar por el último estado activo.
     * Uso: PlanificacionAnual::estadoActual('Pendiente')->get()
     */
    public function scopeEstadoActual($query, string $estado)
    {
        return $query->whereHas('estados', function ($q) use ($estado) {
            $q->where('estado', $estado)
                ->whereRaw('fecha = (
                  SELECT MAX(ea2.fecha) FROM estados_anual ea2
                  WHERE ea2.planificacion_anual_id = estados_anual.planificacion_anual_id
              )');
        });
    }
}
