<?php

namespace App\Repositories;

use App\Repositories\RepositoryInterface;
use Illuminate\Support\Arr;

/**
 * Abstract Respository
 *
 * @package App\Repositories
 * @author Enablestartup <hello@enablestartup.com>
 */
abstract class RepositoryAbstract implements RepositoryInterface
{

    /**
     * Global variable model
     */
    protected $model;

    /**
     * Function construct
     *
     * @param $_model
     */
    public function __construct()
    {
        $this->setModel();
    }

    /**
     * get model
     * @return string
     */
    abstract public function getModel();

    /**
     * Set model
     */
    public function setModel()
    {
        $this->model = app()->make(
            $this->getModel()
        );
    }

    /**
     * Function all (Retrieve all data)
     *
     * @param array $columns
     * @return mixed
     */
    public function all($columns = ['*'])
    {
        return $this->model->all($columns);
    }

    /**
     * Function paginate (Retrieve all data follow paginate)
     *
     * @param integer $limit
     * @param array $columns
     * @return mixed
     */
    public function paginate($limit = 20, $columns = ['*'])
    {
        return $this->model->paginate($limit, $columns);
    }

    /**
     * Function findById
     *
     * @param  array  $attributes (use request->only)
     * @param  integer|string  $id
     * @return mixed
     */
    public function findById($id, $columns = ['*'])
    {
        $result = $this->model->find($id, $columns);
        return $result;
    }

    /**
     * Find data by field and value
     *
     * @param  $field
     * @param  $value
     * @return mixed
     */
    public function findByField($field, $value)
    {
        return $this->model->where($field, $value)->get();
    }

    /**
     * Function create (add a new data)
     *
     * @param  array  $attributes (use request->only)
     * @return mixed
     */
    public function create(array $attributes)
    {
        return $this->model->create($attributes);
    }

    /**
     * Function update (update a current data)
     *
     * @param  array  $attributes (use request->only)
     * @param  integer|string  $id
     * @return boolean
     */
    public function update(array $attributes, $id)
    {
        $result = $this->model->find($id);
        if ($result) {
            $result->fill($attributes);
            if ($result->save()) {
                return true;
            }
            return false;
        }

        return false;
    }

    /**
     * Delete a entity in repository by id
     *
     * @param  integer|string  $id
     * @return boolean
     */
    public function delete($id)
    {
        $result = $this->model->find($id);
        if ($result) {
            $result->delete();
            return true;
        }

        return false;
    }

    /**
     * Order collection by a given column
     *
     * @param  string  $field
     * @param  string  $direction
     * @return $this
     */
    public function orderBy($field, $direction = 'asc')
    {
        return $this->model->orderBy($field, $direction);
    }

    /**
     * Load relations
     *
     * @param  string  $tableName
     * @return $this
     */
    public function with($tableName)
    {
        return $this->model->with($tableName);
    }

    /**
     * Insert data
     *
     * @param array $record
     * @return $this
     */
    public function insert($record)
    {
        return $this->model->insert($record);
    }

    /** Update or Create an entity in repository
     *
     * @param array $attributes
     * @param array $values
     *
     * @return mixed
     */
    public function updateOrCreate(array $attributes, array $values = [])
    {
        return $this->model->updateOrCreate($attributes, $values);
    }

    /** Function get paginate value
     *
     * @param $parameters
     * @return mixed
     */
    public function handlePaginate($parameters)
    {
        $paginate = config('constants.front_end.per_page_default');
        $perPageParam = Arr::only($parameters, ['per_page']);

        if (count($perPageParam) == 0) {
            return $paginate;
        }

        if (!in_array($perPageParam['per_page'], config('constants.front_end.per_page'))) {
            return $paginate;
        }

        return $paginate = (int) $perPageParam['per_page'];
    }

    /** Function handle Sort by when item list
     *
     * @param $query
     * @param $parameters
     * @param $tableName
     * @return mixed
     */
    public function handleSortBy($query, $parameters, $tableName)
    {
        $sortByParam = Arr::only($parameters, ['sort_by']);
        $sortBy = config('constants.front_end.sort_by_default');

        if (count($sortByParam) == 0) {
            return $query->orderBy("$tableName." . $sortBy['column'], $sortBy['sort']);
        }

        if (!array_key_exists($sortByParam['sort_by'], config('constants.front_end.sort_by'))) {
            return $query->orderBy("$tableName." . $sortBy['column'], $sortBy['sort']);
        }

        $sortBy = Arr::get(config('constants.front_end.sort_by'), $sortByParam['sort_by']);
        return $query->orderBy("$tableName." . $sortBy['column'], $sortBy['sort']);
    }
}
