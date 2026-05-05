<?php

declare(strict_types=1);

use MongoDB\BSON\ObjectId;

class WorkoutLogRepository
{
    private ?MongoDB\Collection $collection = null;

    public function __construct(?MongoDB\Database $database)
    {
        if ($database) {
            $this->collection = $database->selectCollection('workout_logs');
        }
    }

    public function create(array $data): void
    {
        if (!$this->collection) {
            return;
        }
        $this->collection->insertOne($data);
    }

    public function findByUser(int $userId): array
    {
        if (!$this->collection) {
            return [];
        }
        return $this->collection
            ->find(['user_id' => $userId], ['sort' => ['training_date' => -1, 'created_at' => -1]])
            ->toArray();
    }

    public function findOne(string $id, int $userId): ?object
    {
        if (!$this->collection || !ObjectId::isValid($id)) {
            return null;
        }

        return $this->collection->findOne([
            '_id' => new ObjectId($id),
            'user_id' => $userId,
        ]);
    }

    public function countByUser(int $userId): int
    {
        if (!$this->collection) {
            return 0;
        }
        return $this->collection->countDocuments(['user_id' => $userId]);
    }

    public function latestByUser(int $userId, int $limit): array
    {
        if (!$this->collection) {
            return [];
        }
        return $this->collection
            ->find(['user_id' => $userId], ['sort' => ['training_date' => -1], 'limit' => $limit])
            ->toArray();
    }
}
