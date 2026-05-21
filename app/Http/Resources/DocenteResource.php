<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocenteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Obtenemos el primer persona_cargo con cargo Docente
        $personaCargo = $this->personaCargos->first();

        return [
            'id'               => $this->id,
            'apellidos'        => $this->apellidos,
            'nombres'          => $this->nombres,
            'nombre_completo'  => "{$this->apellidos}, {$this->nombres}",
            'dni'              => $this->dni,
            'email'            => $this->attributes['e-mail'] ?? null,
            'telefono'         => $this->telefono,
            'direccion'        => $this->direccion,
            'fecha_nacimiento' => $this->fecha_nacimiento,
            'cargo'            => $personaCargo?->cargo?->cargo,
            'sit_revista'      => $personaCargo?->sitRevista?->revista,
            'asignaciones'     => $personaCargo?->personaCargoCursados?->map(function ($asig) {
                return [
                    'id'           => $asig->id,
                    'anio_lectivo' => $asig->cursado?->anio_lectivo,
                    'fecha_inicio' => $asig->cursado?->fecha_inicio,
                    'fecha_fin'    => $asig->cursado?->fecha_fin,
                    'curso'        => [
                        'ciclo'   => $asig->cursado?->curso?->ciclo,
                        'grado'   => $asig->cursado?->curso?->grado,
                        'seccion' => $asig->cursado?->curso?->seccion,
                        'turno'   => $asig->cursado?->curso?->turno,
                    ],
                ];
            }),
        ];
    }
}
