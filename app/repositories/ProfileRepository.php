<?php

declare(strict_types=1);

class ProfileRepository
{
    public function __construct(private mixed $connection = null)
    {
    }

    public function createEmpty(int $userId): void
    {
        postgres_create_empty_profile($userId);
    }

    public function findByUserId(int $userId): ?array
    {
        return postgres_find_profile_by_user_id($userId);
    }

    public function update(int $userId, array $data): void
    {
        postgres_update_profile($userId, $data);
    }

    public function completeOnboarding(int $userId): void
    {
        postgres_complete_onboarding($userId);
    }
}
