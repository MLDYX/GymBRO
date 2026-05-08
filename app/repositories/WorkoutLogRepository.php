<?php

declare(strict_types=1);

class WorkoutLogRepository
{
    public function __construct(private mixed $connection = null)
    {
    }

    public function create(array $data): void
    {
        mongo_create_workout_log($data);
    }

    public function findByUser(int $userId): array
    {
        return mongo_get_workout_logs_by_user($userId);
    }

    public function findOne(string $id, int $userId): ?object
    {
        return mongo_get_workout_log($id, $userId);
    }

    public function countByUser(int $userId): int
    {
        return mongo_count_workout_logs_by_user($userId);
    }

    public function latestByUser(int $userId, int $limit): array
    {
        return mongo_get_latest_workout_logs_by_user($userId, $limit);
    }
}
