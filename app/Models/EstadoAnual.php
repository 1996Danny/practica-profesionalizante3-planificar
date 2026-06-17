<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstadoAnual extends Model
{
    protected $table = 'estados_anual';

    protected $fillable = [
        'estado',
        'fecha',
        'planificacion_anual_id',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    // ─────────────────────────────────────────
    // CONSTANTES DE ESTADOS
    // ─────────────────────────────────────────
    const BORRADOR    = 'BORRADOR';
    const EN_REVISION = 'EN_REVISION';
    const APROBADO    = 'APROBADO';

    // ─────────────────────────────────────────
    // TRANSICIONES PERMITIDAS
    // Desde cada estado, a qué estados se puede ir
    // ─────────────────────────────────────────
    const TRANSICIONES = [
        self::BORRADOR    => [self::EN_REVISION],
        self::EN_REVISION => [self::APROBADO, self::BORRADOR],
        self::APROBADO    => [], // Estado final, no hay transición
    ];

    // ─────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────
    public function planificacionAnual(): BelongsTo
    {
        return $this->belongsTo(PlanificacionAnual::class, 'planificacion_anual_id');
    }
}
