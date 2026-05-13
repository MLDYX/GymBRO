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

    public function all(int $limit = 0): array
    {
        $options = ['sort' => ['created_at' => -1]];
        if ($limit > 0) {
            $options['limit'] = $limit;
        }

        return mongo_admin_list_documents('notifications', [], $options);
    }

    public function findAdmin(string $id): ?object
    {
        return mongo_admin_find_document('notifications', $id);
    }

    public function createAdmin(array $data): ?string
    {
        return mongo_admin_create_document('notifications', $data);
    }

    public function updateAdmin(string $id, array $data): bool
    {
        return mongo_admin_update_document('notifications', $id, $data);
    }

    public function deleteAdmin(string $id): bool
    {
        return mongo_admin_delete_document('notifications', $id);
    }

    public function countAll(): int
    {
        return mongo_admin_count_documents('notifications');
    }
}
