<?php

declare(strict_types=1);

class ProgressRepository
{
    public function __construct(private mixed $connection = null)
    {
    }

    public function create(array $data): void
    {
        mongo_create_progress_measurement($data);
    }

    public function findByUser(int $userId, ?string $type = null): array
    {
        return mongo_get_progress_measurements_by_user($userId, $type);
    }

    public function latestByUser(int $userId, int $limit): array
    {
        return mongo_get_latest_progress_measurements_by_user($userId, $limit);
    }

    public function chartData(int $userId, string $type): array
    {
        return mongo_get_progress_chart_data($userId, $type);
    }

    public function all(?string $type = null, int $limit = 0): array
    {
        $filter = [];
        if ($type !== null && $type !== '') {
            $filter['type'] = $type;
        }

        $options = ['sort' => ['date' => -1, 'created_at' => -1]];
        if ($limit > 0) {
            $options['limit'] = $limit;
        }

        return mongo_admin_list_documents('progress_measurements', $filter, $options);
    }

    public function findAdmin(string $id): ?object
    {
        return mongo_admin_find_document('progress_measurements', $id);
    }

    public function createAdmin(array $data): ?string
    {
        return mongo_admin_create_document('progress_measurements', $data);
    }

    public function updateAdmin(string $id, array $data): bool
    {
        return mongo_admin_update_document('progress_measurements', $id, $data);
    }

    public function deleteAdmin(string $id): bool
    {
        return mongo_admin_delete_document('progress_measurements', $id);
    }

    public function countAll(): int
    {
        return mongo_admin_count_documents('progress_measurements');
    }
}
