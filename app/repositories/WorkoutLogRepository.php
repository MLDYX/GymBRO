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

    public function all(int $limit = 0): array
    {
        $options = ['sort' => ['training_date' => -1, 'created_at' => -1]];
        if ($limit > 0) {
            $options['limit'] = $limit;
        }

        return mongo_admin_list_documents('workout_logs', [], $options);
    }

    public function findAdmin(string $id): ?object
    {
        return mongo_admin_find_document('workout_logs', $id);
    }

    public function updateAdmin(string $id, array $data): bool
    {
        return mongo_admin_update_document('workout_logs', $id, $data);
    }

    public function deleteAdmin(string $id): bool
    {
        return mongo_admin_delete_document('workout_logs', $id);
    }

    public function countAll(): int
    {
        return mongo_admin_count_documents('workout_logs');
    }
}
