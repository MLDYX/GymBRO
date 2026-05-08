<?php

declare(strict_types=1);

class ActivityLogRepository
{
    public function __construct(private mixed $connection = null)
    {
    }

    public function create(int $userId, string $action, array $details): void
    {
        mongo_create_activity_log($userId, $action, $details);
    }

    public function latest(int $userId, int $limit): array
    {
        return mongo_get_latest_activity_logs($userId, $limit);
    }

    public function latestForUsers(array $userIds, int $limit): array
    {
        return mongo_get_latest_activity_logs_for_users($userIds, $limit);
    }
}
