<?php

declare(strict_types=1);

class EventRepository
{
    public function __construct(private mixed $connection = null)
    {
    }

    public function upcoming(): array
    {
        return postgres_get_upcoming_events();
    }

    public function create(int $creatorId, array $data): int
    {
        return postgres_create_event($creatorId, $data);
    }

    public function find(int $id): ?array
    {
        return postgres_find_event($id);
    }

    public function join(int $eventId, int $userId): bool
    {
        return postgres_join_event($eventId, $userId);
    }

    public function leave(int $eventId, int $userId): bool
    {
        return postgres_leave_event($eventId, $userId);
    }

    public function participants(int $eventId): array
    {
        return postgres_get_event_participants($eventId);
    }

    public function countUpcomingForUser(int $userId): int
    {
        return postgres_count_upcoming_events_for_user($userId);
    }

    public function nearestForUser(int $userId): ?array
    {
        return postgres_find_nearest_event_for_user($userId);
    }

    public function sharedEvents(int $userId, int $otherUserId): array
    {
        return postgres_get_shared_events($userId, $otherUserId);
    }

    public function isParticipant(int $eventId, int $userId): bool
    {
        return postgres_is_event_participant($eventId, $userId);
    }
}
