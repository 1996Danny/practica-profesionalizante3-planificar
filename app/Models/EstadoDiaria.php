<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstadoDiaria extends Model
{
    protected $table = 'estados_diaria';

    protected $fillable = [
        'estado',
        'fecha',
        'planificacion_diaria_id',
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
    // ─────────────────────────────────────────
    const TRANSICIONES = [
        self::BORRADOR    => [self::EN_REVISION],
        self::EN_REVISION => [self::APROBADO, self::BORRADOR],
        self::APROBADO    => [],
    ];

    // ─────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────
    public function planificacionDiaria(): BelongsTo
    {
        return $this->belongsTo(PlanificacionDiaria::class, 'planificacion_diaria_id');
    }
}
