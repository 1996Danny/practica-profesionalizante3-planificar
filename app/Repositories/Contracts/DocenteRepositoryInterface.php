<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface DocenteRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Obtener todos los docentes con sus asignaciones completas.
     * Relaciones incluidas: cargo, cursado, curso, sitRevista.
     */
    public function getAll(): Collection;

    /**
     * Obtener un docente por ID con todas sus planificaciones.
     */
    public function findById(int $id): ?object;

    /**
     * Obtener los cursados asignados a un docente.
     * Retorna registros de persona_cargo_cursado.
     */
    public function getCursadosByDocente(int $personaId): Collection;

    /**
     * Obtener docentes asignados a un cursado específico.
     */
    public function getDocentesByCursado(int $cursadoId): Collection;

    /**
     * Obtener docentes activos en un año lectivo.
     */
    public function getByAnioLectivo(string $anioLectivo): Collection;

    /**
     * Buscar docentes por apellido o nombre (búsqueda parcial LIKE).
     */
    public function buscarPorNombre(string $termino): Collection;

    /**
     * Verificar si un docente tiene asignaciones activas.
     */
    public function tieneAsignaciones(int $personaId): bool;
}
