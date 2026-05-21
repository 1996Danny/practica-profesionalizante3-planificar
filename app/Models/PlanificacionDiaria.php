<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanificacionDiaria extends Model
{
    protected $table = 'planificacion_diaria';

    protected $fillable = [
        'fecha_estimada',
        'fecha_desarrollada',
        'fecha_presentacion',
        'contenidos_especificos',
        'actividades',
        'tareas',
        'persona_cargo_cursado_id',
        'tipo_planificacion',
    ];

    protected $casts = [
        'fecha_estimada'    => 'date',
        'fecha_desarrollada' => 'date',
        'fecha_presentacion' => 'date',
    ];

    public function personaCargoCursado(): BelongsTo
    {
        return $this->belongsTo(PersonaCargoCursado::class, 'persona_cargo_cursado_id');
    }

    public function estados(): HasMany
    {
        return $this->hasMany(EstadoDiaria::class, 'planificacion_diaria_id');
    }

    /**
     * Scope para filtrar por el último estado activo.
     */
    public function scopeEstadoActual($query, string $estado)
    {
        return $query->whereHas('estados', function ($q) use ($estado) {
            $q->where('estado', $estado)
                ->whereRaw('fecha = (
                  SELECT MAX(ed2.fecha) FROM estados_diaria ed2
                  WHERE ed2.planificacion_diaria_id = estados_diaria.planificacion_diaria_id
              )');
        });
    }
}
