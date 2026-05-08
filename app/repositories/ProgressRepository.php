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
}
