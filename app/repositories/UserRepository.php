<?php

declare(strict_types=1);

class UserRepository
{
    public function __construct(private mixed $connection = null)
    {
    }

    public function create(string $name, string $email, string $passwordHash): int
    {
        return postgres_create_user($name, $email, $passwordHash);
    }

    public function findByEmail(string $email): ?array
    {
        return postgres_find_user_by_email($email);
    }

    public function findById(int $id): ?array
    {
        return postgres_find_user_by_id($id);
    }

    public function allExcept(int $userId): array
    {
        return postgres_get_all_users_except($userId);
    }

    public function all(): array
    {
        return postgres_get_all_users();
    }

    public function updateAccount(int $id, array $data): bool
    {
        return postgres_update_user_account($id, $data);
    }

    public function delete(int $id): bool
    {
        return postgres_delete_user($id);
    }

    public function countAll(): int
    {
        return postgres_count_users();
    }

    public function latest(int $limit): array
    {
        return postgres_get_latest_users($limit);
    }
}
