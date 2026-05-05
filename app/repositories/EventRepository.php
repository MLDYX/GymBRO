<?php

declare(strict_types=1);

class EventRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function upcoming(): array
    {
        $statement = $this->pdo->query(
            "SELECT we.*, g.name AS gym_name, g.city AS gym_city, u.name AS creator_name,
                    COALESCE(COUNT(wep.id), 0) AS participants_count
             FROM workout_events we
             LEFT JOIN gyms g ON g.id = we.gym_id
             JOIN users u ON u.id = we.creator_id
             LEFT JOIN workout_event_participants wep ON wep.event_id = we.id
             WHERE we.event_date >= CURRENT_DATE AND we.status = 'planned'
             GROUP BY we.id, g.name, g.city, u.name
             ORDER BY we.event_date ASC, we.start_time ASC"
        );

        return $statement->fetchAll();
    }

    public function create(int $creatorId, array $data): int
    {
        $statement = $this->pdo->prepare(
            "INSERT INTO workout_events
             (creator_id, gym_id, title, description, event_date, start_time, max_participants, status)
             VALUES (:creator_id, :gym_id, :title, :description, :event_date, :start_time, :max_participants, 'planned')
             RETURNING id"
        );
        $statement->execute([
            'creator_id' => $creatorId,
            'gym_id' => $data['gym_id'] !== '' ? (int) $data['gym_id'] : null,
            'title' => trim((string) $data['title']),
            'description' => trim((string) $data['description']) ?: null,
            'event_date' => $data['event_date'],
            'start_time' => $data['start_time'],
            'max_participants' => max(2, (int) $data['max_participants']),
        ]);

        return (int) $statement->fetchColumn();
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            "SELECT we.*, g.name AS gym_name, g.city AS gym_city, g.address AS gym_address, u.name AS creator_name,
                    COALESCE(COUNT(wep.id), 0) AS participants_count
             FROM workout_events we
             LEFT JOIN gyms g ON g.id = we.gym_id
             JOIN users u ON u.id = we.creator_id
             LEFT JOIN workout_event_participants wep ON wep.event_id = we.id
             WHERE we.id = :id
             GROUP BY we.id, g.name, g.city, g.address, u.name
             LIMIT 1"
        );
        $statement->execute(['id' => $id]);
        $event = $statement->fetch();

        if (!$event) {
            return null;
        }

        $event['participants'] = $this->participants($id);
        $event['free_slots'] = max(0, (int) $event['max_participants'] - (int) $event['participants_count']);

        return $event;
    }

    public function join(int $eventId, int $userId): bool
    {
        $event = $this->find($eventId);
        if (!$event || (int) $event['free_slots'] <= 0) {
            return false;
        }

        if ($this->isParticipant($eventId, $userId)) {
            return false;
        }

        $statement = $this->pdo->prepare(
            'INSERT INTO workout_event_participants (event_id, user_id) VALUES (:event_id, :user_id)'
        );

        return $statement->execute(['event_id' => $eventId, 'user_id' => $userId]);
    }

    public function leave(int $eventId, int $userId): bool
    {
        $statement = $this->pdo->prepare(
            'DELETE FROM workout_event_participants WHERE event_id = :event_id AND user_id = :user_id'
        );
        $statement->execute(['event_id' => $eventId, 'user_id' => $userId]);

        return $statement->rowCount() > 0;
    }

    public function participants(int $eventId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT u.id, u.name, u.email
             FROM workout_event_participants wep
             JOIN users u ON u.id = wep.user_id
             WHERE wep.event_id = :event_id
             ORDER BY u.name ASC'
        );
        $statement->execute(['event_id' => $eventId]);

        return $statement->fetchAll();
    }

    public function countUpcomingForUser(int $userId): int
    {
        $statement = $this->pdo->prepare(
            "SELECT COUNT(*)
             FROM workout_events
             WHERE creator_id = :user_id AND event_date >= CURRENT_DATE AND status = 'planned'"
        );
        $statement->execute(['user_id' => $userId]);

        return (int) $statement->fetchColumn();
    }

    public function nearestForUser(int $userId): ?array
    {
        $statement = $this->pdo->prepare(
            "SELECT we.*, g.name AS gym_name
             FROM workout_events we
             LEFT JOIN gyms g ON g.id = we.gym_id
             WHERE (we.creator_id = :user_id OR EXISTS (
                    SELECT 1 FROM workout_event_participants wep
                    WHERE wep.event_id = we.id AND wep.user_id = :user_id
             ))
               AND we.event_date >= CURRENT_DATE
               AND we.status = 'planned'
             ORDER BY we.event_date ASC, we.start_time ASC
             LIMIT 1"
        );
        $statement->execute(['user_id' => $userId]);
        $event = $statement->fetch();

        return $event ?: null;
    }

    public function sharedEvents(int $userId, int $otherUserId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT DISTINCT we.id, we.title, we.event_date, we.start_time
             FROM workout_events we
             JOIN workout_event_participants p1 ON p1.event_id = we.id
             JOIN workout_event_participants p2 ON p2.event_id = we.id
             WHERE p1.user_id = :user_id AND p2.user_id = :other_user_id
             ORDER BY we.event_date ASC, we.start_time ASC
             LIMIT 5"
        );
        $statement->execute(['user_id' => $userId, 'other_user_id' => $otherUserId]);

        return $statement->fetchAll();
    }

    public function isParticipant(int $eventId, int $userId): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT 1 FROM workout_event_participants WHERE event_id = :event_id AND user_id = :user_id LIMIT 1'
        );
        $statement->execute(['event_id' => $eventId, 'user_id' => $userId]);

        return (bool) $statement->fetchColumn();
    }
}
