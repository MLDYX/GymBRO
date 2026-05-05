<?php

declare(strict_types=1);

use MongoDB\BSON\ObjectId as MongoObjectId;

class NotificationRepository
{
    private ?MongoDB\Collection $collection = null;

    public function __construct(?MongoDB\Database $database)
    {
        if ($database) {
            $this->collection = $database->selectCollection('notifications');
        }
    }

    public function create(int $userId, string $type, string $title, string $content): void
    {
        if (!$this->collection) {
            return;
        }
        $this->collection->insertOne([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'content' => $content,
            'is_read' => false,
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

    public function markAsRead(string $id, int $userId): bool
    {
        if (!$this->collection || !MongoObjectId::isValid($id)) {
            return false;
        }

        $result = $this->collection->updateOne(
            ['_id' => new MongoObjectId($id), 'user_id' => $userId],
            ['$set' => ['is_read' => true]]
        );

        return $result->getModifiedCount() > 0;
    }
}
