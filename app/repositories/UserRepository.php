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
}
