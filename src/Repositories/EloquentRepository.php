<?php

namespace Raoby\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Raoby\Contracts\Repositories\RepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

abstract class EloquentRepository implements RepositoryContract
{
    protected array $eagerLoaded = [];

    protected ?string $orderByField  = null;

    protected string $orderDirection = 'asc';

    /**
     * Instantiate the repository
     *
     * @param Model $model
     */
    public function __construct(protected Model $model)
    {

    }

    /**
     * Define the eager loaded relationship
     *
     * @param array $relations
     * @return RepositoryContract
     */
    public function with(array $relations): RepositoryContract
    {
        $this->eagerLoaded = $relations;
        return $this;
    }

    /**
     * Define the order
     *
     * @param string $field
     * @param string $order
     * @return void
     */
    public function orderBy(string $field, string $order = 'asc'): RepositoryContract
    {
        $this->orderByField   = $field;
        $this->orderDirection = $order;
        return $this;
    }

    /**
     * Find all items from database
     *
     * @param integer|null $length
     * @param boolean $paginate
     * @return Collection|LengthAwarePaginator
     */
    public function findAll(?int $length = null, bool $paginate = false): Collection|LengthAwarePaginator
    {
        $query = $this->model->with($this->eagerLoaded);

        if ($this->orderByField) {
            $query->orderBy($this->orderByField, $this->orderDirection);
        }

        if ($paginate) {
            return $query->paginate($length ?? 15)->withQueryString();
        }

        return $length ? $query->limit($length)->get() : $query->get();
    }

    /**
     * Find item by identifier from database
     *
     * @param string|integer $id
     * @return Model|null
     */
    public function findOne(string|int $id): ?Model
    {
        return $this->model
            ->with($this->eagerLoaded)
            ->find($id);
    }

    /**
     * Find item by custom field from database
     *
     * @param string $field
     * @param mixed $value
     * @return Model|null
     */
    public function findOneBy(string $field, $value): ?Model
    {
        return $this->model
            ->with($this->eagerLoaded)
            ->whereIn($field, $value)
            ->first();
    }

    /**
     * Save item into database
     *
     * @param Model $model
     * @return Model|null
     */
    public function save(Model $model): ?Model
    {
        try {
            if ($model->save()) {
                return $model;
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage(), $th->getTrace());
        }

        return null;
    }

    /**
     * Delete item from database
     *
     * @param Model $model
     * @return boolean
     */
    public function delete(Model $model): bool
    {
        try {
            if($model->delete()) {
                return true;
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage(), $th->getTrace());
        }

        return false;
    }
}
