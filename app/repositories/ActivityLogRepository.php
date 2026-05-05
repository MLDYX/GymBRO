<?php

declare(strict_types=1);

class ActivityLogRepository
{
    private ?MongoDB\Collection $collection = null;

    public function __construct(?MongoDB\Database $database)
    {
        if ($database) {
            $this->collection = $database->selectCollection('activity_logs');
        }
    }

    public function create(int $userId, string $action, array $details): void
    {
        if (!$this->collection) {
            return;
        }
        $this->collection->insertOne([
            'user_id' => $userId,
            'action' => $action,
            'details' => $details,
            'created_at' => now_string(),
        ]);
    }

    public function latest(int $userId, int $limit): array
    {
        if (!$this->collection) {
            return [];
        }
        return $this->collection
            ->find(['user_id' => $userId], ['sort' => ['created_at' => -1], 'limit' => $limit])
            ->toArray();
    }

    public function latestForUsers(array $userIds, int $limit): array
    {
        if (!$this->collection || $userIds === []) {
            return [];
        }

        return $this->collection
            ->find(['user_id' => ['$in' => array_values($userIds)]], ['sort' => ['created_at' => -1], 'limit' => $limit])
            ->toArray();
    }
}
