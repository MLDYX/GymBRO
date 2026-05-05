<?php

declare(strict_types=1);

class ExerciseRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function all(): array
    {
        $statement = $this->pdo->query(
            'SELECT e.*, u.name AS owner_name
             FROM exercises e
             LEFT JOIN users u ON u.id = e.user_id
             ORDER BY e.created_at DESC'
        );

        return $statement->fetchAll();
    }

    public function create(int $userId, array $data): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO exercises (user_id, name, muscle_group, equipment, description)
             VALUES (:user_id, :name, :muscle_group, :equipment, :description)'
        );
        $statement->execute([
            'user_id' => $userId,
            'name' => trim((string) $data['name']),
            'muscle_group' => trim((string) $data['muscle_group']),
            'equipment' => trim((string) $data['equipment']) ?: null,
            'description' => trim((string) $data['description']) ?: null,
        ]);
    }

    public function update(int $id, int $userId, array $data): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE exercises
             SET name = :name, muscle_group = :muscle_group, equipment = :equipment, description = :description
             WHERE id = :id AND user_id = :user_id'
        );
        $statement->execute([
            'id' => $id,
            'user_id' => $userId,
            'name' => trim((string) $data['name']),
            'muscle_group' => trim((string) $data['muscle_group']),
            'equipment' => trim((string) $data['equipment']) ?: null,
            'description' => trim((string) $data['description']) ?: null,
        ]);

        return $statement->rowCount() > 0;
    }

    public function delete(int $id, int $userId): bool
    {
        $statement = $this->pdo->prepare('DELETE FROM exercises WHERE id = :id AND user_id = :user_id');
        $statement->execute(['id' => $id, 'user_id' => $userId]);

        return $statement->rowCount() > 0;
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT e.*, u.name AS owner_name
             FROM exercises e
             LEFT JOIN users u ON u.id = e.user_id
             WHERE e.id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $exercise = $statement->fetch();

        return $exercise ?: null;
    }
}
