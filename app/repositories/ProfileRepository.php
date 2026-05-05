<?php

declare(strict_types=1);

class ProfileRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function createEmpty(int $userId): void
    {
        $statement = $this->pdo->prepare('INSERT INTO user_profiles (user_id) VALUES (:user_id) ON CONFLICT (user_id) DO NOTHING');
        $statement->execute(['user_id' => $userId]);
    }

    public function findByUserId(int $userId): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM user_profiles WHERE user_id = :user_id LIMIT 1');
        $statement->execute(['user_id' => $userId]);
        $profile = $statement->fetch();

        return $profile ?: null;
    }

    public function update(int $userId, array $data): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE user_profiles
             SET age = :age,
                 height_cm = :height_cm,
                 weight_kg = :weight_kg,
                 training_level = :training_level,
                 goal = :goal,
                 bio = :bio,
                 updated_at = CURRENT_TIMESTAMP
             WHERE user_id = :user_id'
        );
        $statement->execute([
            'user_id' => $userId,
            'age' => $data['age'] !== '' ? (int) $data['age'] : null,
            'height_cm' => $data['height_cm'] !== '' ? (int) $data['height_cm'] : null,
            'weight_kg' => $data['weight_kg'] !== '' ? $data['weight_kg'] : null,
            'training_level' => trim((string) $data['training_level']) ?: null,
            'goal' => trim((string) $data['goal']) ?: null,
            'bio' => trim((string) $data['bio']) ?: null,
        ]);
    }
}
