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
        $existingProfile = $this->findByUserId($userId) ?? [];
        $onboardingCompleted = array_key_exists('onboarding_completed', $data)
            ? (bool) $data['onboarding_completed']
            : (bool) ($existingProfile['onboarding_completed'] ?? false);

        $statement = $this->pdo->prepare(
            'UPDATE user_profiles
             SET age = :age,
                 height_cm = :height_cm,
                 weight_kg = :weight_kg,
                 training_level = :training_level,
                 training_experience = :training_experience,
                 goal = :goal,
                 bio = :bio,
                 avatar_path = :avatar_path,
                 onboarding_completed = :onboarding_completed,
                 updated_at = CURRENT_TIMESTAMP
             WHERE user_id = :user_id'
        );

        $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $statement->bindValue(':age', $data['age'] !== '' ? (int) $data['age'] : null, $data['age'] !== '' ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $statement->bindValue(':height_cm', $data['height_cm'] !== '' ? (int) $data['height_cm'] : null, $data['height_cm'] !== '' ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $statement->bindValue(':weight_kg', $data['weight_kg'] !== '' ? (string) $data['weight_kg'] : null, $data['weight_kg'] !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $statement->bindValue(':training_level', trim((string) ($data['training_level'] ?? '')) ?: null, trim((string) ($data['training_level'] ?? '')) !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $statement->bindValue(':training_experience', trim((string) ($data['training_experience'] ?? '')) ?: null, trim((string) ($data['training_experience'] ?? '')) !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $statement->bindValue(':goal', trim((string) ($data['goal'] ?? '')) ?: null, trim((string) ($data['goal'] ?? '')) !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $statement->bindValue(':bio', trim((string) ($data['bio'] ?? '')) ?: null, trim((string) ($data['bio'] ?? '')) !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $statement->bindValue(':avatar_path', trim((string) ($data['avatar_path'] ?? '')) ?: null, trim((string) ($data['avatar_path'] ?? '')) !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $statement->bindValue(':onboarding_completed', $onboardingCompleted, PDO::PARAM_BOOL);

        $statement->execute();
    }

    public function completeOnboarding(int $userId): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE user_profiles SET onboarding_completed = TRUE, updated_at = CURRENT_TIMESTAMP WHERE user_id = :user_id'
        );
        $statement->execute(['user_id' => $userId]);
    }
}
