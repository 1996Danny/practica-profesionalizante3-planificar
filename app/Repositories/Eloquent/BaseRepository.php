<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Clase abstracta base.
 * Implementa el CRUD genérico para evitar repetir código.
 * Cada repositorio concreto la extiende y agrega sus métodos específicos.
 */
abstract class BaseRepository implements BaseRepositoryInterface
{
    public function __construct(
        protected Model $model
    ) {}

    public function getAll(): Collection
    {
        return $this->model->all();
    }

    public function findById(int $id): ?object
    {
        return $this->model->find($id);
    }

    public function create(array $data): object
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): object
    {
        $registro = $this->model->findOrFail($id);
        $registro->update($data);
        return $registro->fresh();
    }

    public function delete(int $id): bool
    {
        $registro = $this->model->findOrFail($id);
        return (bool) $registro->delete();
    }

    public function paginate(int $perPage = 15, array $filtros = []): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }
}
