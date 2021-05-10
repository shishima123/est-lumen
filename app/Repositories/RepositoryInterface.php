<?php

namespace App\Repositories;

/**
 * Interface RepositoryInterface
 *
 * @package App\Repositories
 */
interface RepositoryInterface
{
    /**
     * Retrieve all data of repository
     *
     * @param array $columns
     * @return mixed
     */
    public function all($columns = ['*']);

    /**
     * Retrieve all data of repository, paginated
     *
     * @param integer $limit
     * @param array $columns
     * @return mixed
     */
    public function paginate($limit = 20, $columns = ['*']);

    /**
     * Find data by id
     *
     * @param  integer|string  $id
     * @param array $columns
     * @return mixed
     */
    public function findById($id, $columns = ['*']);

    /**
     * Find data by field and value
     *
     * @param  string  $field
     * @param  string  $value
     * @return mixed
     */
    public function findByField($field, $value);

    /**
     * Save a new entity in repository
     *
     * @param  array  $attributes
     * @return mixed
     */
    public function create(array $attributes);

    /**
     * Update a entity in repository by id
     *
     * @param  array  $attributes
     * @param  integer|string  $id
     * @return mixed
     */
    public function update(array $attributes, $id);

    /**
     * Delete a entity in repository by id
     *
     * @param  integer|string  $id
     * @return mixed
     */
    public function delete($id);

    /**
     * Order collection by a given column
     *
     * @param  string  $field
     * @param  string  $direction
     * @return $this
     */
    public function orderBy($field, $direction = 'asc');

    /**
     * Load relations
     *
     * @param  $tableName
     * @return $this
     */
    public function with($tableName);

    /**
     * Insert data
     *
     * @param array $record
     * @return $this
     */
    public function insert($record);

    /** Update or Create an entity in repository
     *
     * @param array $attributes
     * @param array $values
     *
     * @return mixed
     */
    public function updateOrCreate(array $attributes, array $values = []);

    /** Function get paginate value
     *
     * @param $parameters
     * @return mixed
     */
    public function handlePaginate($parameters);

    /** Function handle Sort by when item list
     *
     * @param $query
     * @param $parameters
     * @param $tableName
     * @return mixed
     */
    public function handleSortBy($query, $parameters, $tableName);
}
