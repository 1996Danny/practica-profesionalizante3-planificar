<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Curso extends Model
{
    protected $table = 'cursos';

    protected $fillable = [
        'ciclo',
        'grado',
        'seccion',
        'turno',
    ];

    public function cursados(): HasMany
    {
        return $this->hasMany(Cursado::class, 'cursos_id');
    }
}
