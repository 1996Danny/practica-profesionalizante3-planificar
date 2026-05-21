<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    protected $table = 'areas';

    protected $fillable = ['area', 'tipo'];

    public function planificacionesAnuales(): HasMany
    {
        return $this->hasMany(PlanificacionAnual::class, 'areas_id');
    }
}
