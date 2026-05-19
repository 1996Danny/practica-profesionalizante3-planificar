<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Contrato base con operaciones CRUD genéricas.
 * Las interfaces específicas extenderán este contrato.
 */
interface BaseRepositoryInterface
{
    public function getAll(): Collection;
    public function findById(int $id): ?object;
    public function create(array $data): object;
    public function update(int $id, array $data): object;
    public function delete(int $id): bool;
    public function paginate(int $perPage = 15, array $filtros = []): LengthAwarePaginator;
}
