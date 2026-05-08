<?php

declare(strict_types=1);

class NotificationRepository
{
    public function __construct(private mixed $connection = null)
    {
    }

    public function create(int $userId, string $type, string $title, string $content): void
    {
        mongo_create_notification($userId, $type, $title, $content);
    }

    public function latest(int $userId, int $limit): array
    {
        return mongo_get_latest_notifications($userId, $limit);
    }

    public function markAsRead(string $id, int $userId): bool
    {
        return mongo_mark_notification_as_read($id, $userId);
    }
}
