<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonaCargo extends Model
{
    protected $table = 'persona_cargos';

    protected $fillable = [
        'personas_id',
        'cargos_id',
        'sit_revista_id',
    ];

    // ─────────────────────────────────────────
    // RELACIONES HACIA ARRIBA (BelongsTo)
    // ─────────────────────────────────────────

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'personas_id');
    }

    public function cargo(): BelongsTo
    {
        return $this->belongsTo(Cargo::class, 'cargos_id');
    }

    public function sitRevista(): BelongsTo
    {
        return $this->belongsTo(SitRevista::class, 'sit_revista_id');
    }

    // ─────────────────────────────────────────
    // RELACIONES HACIA ABAJO (HasMany)
    // ─────────────────────────────────────────

    public function personaCargoCursados(): HasMany
    {
        return $this->hasMany(PersonaCargoCursado::class, 'persona_cargos_id');
    }
}
