<?php

namespace Raoby\Contracts\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface RepositoryContract
{
    /**
     * Define the eager loaded relationship
     *
     * @param array $relations
     * @return RepositoryContract
     */
    public function with(array $relations): RepositoryContract;

    /**
     * Define the order
     *
     * @param string $field
     * @param string $order
     * @return void
     */
    public function orderBy(string $field, string $order = 'asc'): RepositoryContract;

    /**
     * Find all items from database
     *
     * @param integer|null $length
     * @param boolean $paginate
     * @return Collection|LengthAwarePaginator
     */
    public function findAll(?int $length = null, bool $paginate = false): Collection|LengthAwarePaginator;

    /**
     * Find item by identifier from database
     *
     * @param string|integer $id
     * @return Model|null
     */
    public function findOne(string|int $id): ?Model;

    /**
     * Find item by custom field from database
     *
     * @param string $field
     * @param mixed $value
     * @return Model|null
     */
    public function findOneBy(string $field, $value): ?Model;

    /**
     * Save item into database
     *
     * @param Model $model
     * @return Model|null
     */
    public function save(Model $model): ?Model;

    /**
     * Delete item from database
     *
     * @param Model $model
     * @return boolean
     */
    public function delete(Model $model): bool;
}
