<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Persona extends Model
{
    use SoftDeletes;

    protected $table = 'personas';

    protected $fillable = [
        'apellidos',
        'nombres',
        'dni',
        'e-mail',
        'telefono',
        'direccion',
        'fecha_nacimiento',
    ];

    protected $hidden = ['deleted_at'];

    // ─────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function personaCargos(): HasMany
    {
        return $this->hasMany(PersonaCargo::class, 'personas_id');
    }

    // ─────────────────────────────────────────
    // ACCESSORS
    // ─────────────────────────────────────────

    /**
     * Accessor principal para el email
     * Uso: $persona->email
     */
    public function getEmailAttribute(): string
    {
        return $this->attributes['e-mail'] ?? '';
    }

    /**
     * Accessor alternativo
     * Uso: $persona->email_address
     */
    public function getEmailAddressAttribute(): string
    {
        return $this->attributes['e-mail'] ?? '';
    }
}
