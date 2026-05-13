<?php

declare(strict_types=1);

class GymRepository
{
    public function __construct(private mixed $connection = null)
    {
    }

    public function all(): array
    {
        return postgres_get_all_gyms();
    }

    public function create(int $userId, array $data): void
    {
        postgres_create_gym($userId, $data);
    }

    public function update(int $id, int $userId, array $data): bool
    {
        return postgres_update_gym($id, $userId, $data);
    }

    public function delete(int $id, int $userId): bool
    {
        return postgres_delete_gym($id, $userId);
    }

    public function find(int $id): ?array
    {
        return postgres_find_gym($id);
    }

    public function updateAdmin(int $id, array $data): bool
    {
        return postgres_update_gym_admin($id, $data);
    }

    public function deleteAdmin(int $id): bool
    {
        return postgres_delete_gym_admin($id);
    }

    public function countAll(): int
    {
        return postgres_count_gyms();
    }
}
