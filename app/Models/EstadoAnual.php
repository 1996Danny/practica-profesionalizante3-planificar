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

    public function planificacionAnual(): BelongsTo
    {
        return $this->belongsTo(PlanificacionAnual::class, 'planificacion_anual_id');
    }
}
