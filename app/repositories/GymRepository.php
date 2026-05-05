<?php

declare(strict_types=1);

class GymRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function all(): array
    {
        $statement = $this->pdo->query(
            'SELECT g.*, u.name AS owner_name
             FROM gyms g
             LEFT JOIN users u ON u.id = g.user_id
             ORDER BY g.created_at DESC'
        );

        return $statement->fetchAll();
    }

    public function create(int $userId, array $data): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO gyms (user_id, name, city, address, description)
             VALUES (:user_id, :name, :city, :address, :description)'
        );
        $statement->execute([
            'user_id' => $userId,
            'name' => trim((string) $data['name']),
            'city' => trim((string) $data['city']),
            'address' => trim((string) $data['address']) ?: null,
            'description' => trim((string) $data['description']) ?: null,
        ]);
    }

    public function update(int $id, int $userId, array $data): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE gyms
             SET name = :name, city = :city, address = :address, description = :description
             WHERE id = :id AND user_id = :user_id'
        );
        $statement->execute([
            'id' => $id,
            'user_id' => $userId,
            'name' => trim((string) $data['name']),
            'city' => trim((string) $data['city']),
            'address' => trim((string) $data['address']) ?: null,
            'description' => trim((string) $data['description']) ?: null,
        ]);

        return $statement->rowCount() > 0;
    }

    public function delete(int $id, int $userId): bool
    {
        $statement = $this->pdo->prepare('DELETE FROM gyms WHERE id = :id AND user_id = :user_id');
        $statement->execute(['id' => $id, 'user_id' => $userId]);

        return $statement->rowCount() > 0;
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT g.*, u.name AS owner_name
             FROM gyms g
             LEFT JOIN users u ON u.id = g.user_id
             WHERE g.id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $gym = $statement->fetch();

        return $gym ?: null;
    }
}
