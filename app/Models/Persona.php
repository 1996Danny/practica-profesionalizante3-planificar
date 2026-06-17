<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Persona extends Model
{
    use SoftDeletes;

    protected $table = 'personas';

    protected $fillable = [
        'apellidos',
        'nombres',
        'dni',
        'e-mail',       // ← campo con guión, se mapea normalmente
        'telefono',
        'direccion',
        'fecha_nacimiento',
    ];

    protected $hidden = ['deleted_at'];

    public function user()
    {
        return $this->hasOne(User::class);
    }

    // ─────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────

    /**
     * Una persona puede tener muchos cargos asignados.
     */
    public function personaCargos(): HasMany
    {
        return $this->hasMany(PersonaCargo::class, 'personas_id');
    }

    // ─────────────────────────────────────────
    // ACCESSORS (para manejar el campo 'e-mail')
    // ─────────────────────────────────────────

    /**
     * Accessor para acceder al email sin usar la sintaxis de guión.
     * Uso: $persona->email_address
     */
    public function getEmailAddressAttribute(): string
    {
        return $this->attributes['e-mail'] ?? '';
    }
}
