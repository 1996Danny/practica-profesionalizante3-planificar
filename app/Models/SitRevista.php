<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SitRevista extends Model
{
    protected $table = 'sit_revista';

    protected $fillable = ['revista'];

    public function personaCargos(): HasMany
    {
        return $this->hasMany(PersonaCargo::class, 'sit_revista_id');
    }
}
