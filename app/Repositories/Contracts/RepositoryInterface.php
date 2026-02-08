<?php

namespace App\Repositories\Contracts;

interface RepositoryInterface
{
    /**
     * Get all records with pagination
     */
    public function paginate(int $perPage = 15, array $columns = ['*']);

    /**
     * Get all records
     */
    public function all(array $columns = ['*']);

    /**
     * Get a record by ID
     */
    public function find(int $id, array $columns = ['*']);

    /**
     * Create a new record
     */
    public function create(array $data);

    /**
     * Update a record
     */
    public function update(int $id, array $data);

    /**
     * Delete a record
     */
    public function delete(int $id);

    /**
     * Find by multiple conditions
     */
    public function findWhere(array $conditions, array $columns = ['*']);
}
