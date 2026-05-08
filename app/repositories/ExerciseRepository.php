<?php

declare(strict_types=1);

class ExerciseRepository
{
    public function __construct(private mixed $connection = null)
    {
    }

    public function all(): array
    {
        return postgres_get_all_exercises();
    }

    public function create(int $userId, array $data): void
    {
        postgres_create_exercise($userId, $data);
    }

    public function update(int $id, int $userId, array $data): bool
    {
        return postgres_update_exercise($id, $userId, $data);
    }

    public function delete(int $id, int $userId): bool
    {
        return postgres_delete_exercise($id, $userId);
    }

    public function find(int $id): ?array
    {
        return postgres_find_exercise($id);
    }
}
