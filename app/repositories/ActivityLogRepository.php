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

    public function all(int $limit = 0): array
    {
        $options = ['sort' => ['created_at' => -1]];
        if ($limit > 0) {
            $options['limit'] = $limit;
        }

        return mongo_admin_list_documents('activity_logs', [], $options);
    }

    public function findAdmin(string $id): ?object
    {
        return mongo_admin_find_document('activity_logs', $id);
    }

    public function createAdmin(array $data): ?string
    {
        return mongo_admin_create_document('activity_logs', $data);
    }

    public function updateAdmin(string $id, array $data): bool
    {
        return mongo_admin_update_document('activity_logs', $id, $data);
    }

    public function deleteAdmin(string $id): bool
    {
        return mongo_admin_delete_document('activity_logs', $id);
    }

    public function countAll(): int
    {
        return mongo_admin_count_documents('activity_logs');
    }
}
