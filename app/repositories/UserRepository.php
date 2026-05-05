<?php

declare(strict_types=1);

class UserRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(string $name, string $email, string $passwordHash): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :password_hash) RETURNING id'
        );
        $statement->execute([
            'name' => $name,
            'email' => mb_strtolower($email),
            'password_hash' => $passwordHash,
        ]);

        return (int) $statement->fetchColumn();
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $statement->execute(['email' => mb_strtolower($email)]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT u.*, up.age, up.height_cm, up.weight_kg, up.training_level, up.goal, up.bio
             FROM users u
             LEFT JOIN user_profiles up ON up.user_id = u.id
             WHERE u.id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function allExcept(int $userId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT u.id, u.name, u.email, up.training_level, up.goal, up.bio
             FROM users u
             LEFT JOIN user_profiles up ON up.user_id = u.id
             WHERE u.id <> :user_id
             ORDER BY u.name ASC'
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }
}
