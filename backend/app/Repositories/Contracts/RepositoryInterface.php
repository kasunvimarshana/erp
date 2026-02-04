<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface RepositoryInterface
{
    /**
     * Get all records.
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Get paginated records.
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;

    /**
     * Find a record by ID.
     */
    public function find(string $id, array $columns = ['*']): ?Model;

    /**
     * Find a record by ID or fail.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(string $id, array $columns = ['*']): Model;

    /**
     * Find records by specific field.
     */
    public function findBy(string $field, mixed $value, array $columns = ['*']): Collection;

    /**
     * Find a single record by specific field.
     */
    public function findOneBy(string $field, mixed $value, array $columns = ['*']): ?Model;

    /**
     * Create a new record.
     */
    public function create(array $data): Model;

    /**
     * Update a record.
     */
    public function update(string $id, array $data): bool;

    /**
     * Delete a record.
     */
    public function delete(string $id): bool;

    /**
     * Count records.
     */
    public function count(): int;

    /**
     * Check if record exists.
     */
    public function exists(string $id): bool;
}
