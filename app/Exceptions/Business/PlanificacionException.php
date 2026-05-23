<?php

namespace App\Exceptions\Business;

use Exception;

class PlanificacionException extends Exception
{
    public static function yaExistePlanificacionActiva(
        int $cursadoId,
        int $areaId
    ): self {
        return new self(
            "Ya existe una planificación anual activa para el cursado "
                . "{$cursadoId} y área {$areaId} en estado EN_REVISION o APROBADO.",
            409
        );
    }

    public static function documentoInmutable(string $estado): self
    {
        return new self(
            "La planificación no puede modificarse porque está en estado "
                . "'{$estado}'. Solo se puede editar en estado BORRADOR.",
            403
        );
    }

    public static function documentoBloqueado(): self
    {
        return new self(
            "La planificación está en revisión y no puede ser "
                . "modificada ni eliminada por el docente.",
            403
        );
    }

    public static function fechaFueraDelPeriodoLectivo(
        string $fecha,
        string $inicio,
        string $fin
    ): self {
        return new self(
            "La fecha {$fecha} está fuera del período lectivo "
                . "({$inicio} - {$fin}).",
            422
        );
    }

    public static function fechaDuplicadaEnCursado(
        string $fecha,
        int $cursadoId
    ): self {
        return new self(
            "Ya existe una planificación diaria para la fecha "
                . "{$fecha} en el cursado {$cursadoId}.",
            409
        );
    }

    public static function docenteNoAsignado(
        int $docenteId,
        int $cursadoId
    ): self {
        return new self(
            "El docente {$docenteId} no está asignado al "
                . "cursado {$cursadoId}.",
            403
        );
    }

    public static function planificacionNoEncontrada(int $id): self
    {
        return new self(
            "No se encontró la planificación con ID {$id}.",
            404
        );
    }
}
