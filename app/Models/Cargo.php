<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cargo extends Model
{
    protected $table = 'cargos';

    protected $fillable = ['cargo'];

    public function personaCargos(): HasMany
    {
        return $this->hasMany(PersonaCargo::class, 'cargos_id');
    }
}
