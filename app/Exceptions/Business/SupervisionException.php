<?php

namespace App\Exceptions\Business;

use Exception;

class SupervisionException extends Exception
{
    public static function planificacionNoEnRevision(int $id): self
    {
        return new self(
            "La planificación {$id} no está en estado EN_REVISION. "
                . "Solo se pueden evaluar planificaciones en ese estado.",
            422
        );
    }

    public static function observacionesObligatorias(): self
    {
        return new self(
            "El campo 'observaciones' es obligatorio cuando se "
                . "rechaza una planificación.",
            422
        );
    }

    public static function estadoInvalidoParaDirector(string $estado): self
    {
        return new self(
            "El estado '{$estado}' no es válido. El director solo puede "
                . "transicionar a APROBADO o BORRADOR (rechazo).",
            422
        );
    }

    public static function directorSinPermiso(int $directorId): self
    {
        return new self(
            "El director {$directorId} no tiene permiso para "
                . "evaluar esta planificación.",
            403
        );
    }

    public static function docenteSuplenteDuplicado(
        int $cursadoId,
        int $docenteId
    ): self {
        return new self(
            "El docente {$docenteId} ya está asignado al "
                . "cursado {$cursadoId}.",
            409
        );
    }
}
