<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanificacionAnualResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $asignacion  = $this->personaCargoCursado;
        $personaCargo = $asignacion?->personaCargo;
        $persona     = $personaCargo?->persona;
        $cursado     = $asignacion?->cursado;
        $estadoActual = $this->estados?->first(); // ya ordenado por fecha desc

        return [
            'id'                     => $this->id,
            'tipo'                   => 'anual',
            'tipo_planificacion'     => $this->tipo_planificacion,
            'fecha_presentacion'     => $this->fecha_presentacion,
            'estado_actual'          => [
                'estado' => $estadoActual?->estado ?? 'Sin estado',
                'fecha'  => $estadoActual?->fecha,
            ],
            'area'                   => [
                'id'   => $this->area?->id,
                'area' => $this->area?->area,
                'tipo' => $this->area?->tipo,
            ],
            'docente'                => [
                'id'             => $persona?->id,
                'nombre_completo' => $persona
                    ? "{$persona->apellidos}, {$persona->nombres}"
                    : null,
                'dni'            => $persona?->dni,
            ],
            'cursado'                => [
                'anio_lectivo' => $cursado?->anio_lectivo,
                'grado'        => $cursado?->curso?->grado,
                'seccion'      => $cursado?->curso?->seccion,
                'turno'        => $cursado?->curso?->turno,
            ],
            'contenido'              => [
                'aprendizajes_esperados' => $this->aprendizajes_esperados,
                'saberes'                => $this->saberes,
                'criterios'              => $this->criterios,
                'bibliografia'           => $this->bibliografia,
                'diagnostico'            => $this->diagnostico,
            ],
            'historial_estados'      => $this->estados?->map(fn($e) => [
                'estado' => $e->estado,
                'fecha'  => $e->fecha,
            ]),
        ];
    }
}
