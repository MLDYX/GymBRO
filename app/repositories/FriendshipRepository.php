<?php

declare(strict_types=1);

class FriendshipRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function sendRequest(int $requesterId, int $receiverId): bool
    {
        if ($requesterId === $receiverId || $this->areFriendsOrPending($requesterId, $receiverId)) {
            return false;
        }

        $statement = $this->pdo->prepare(
            'INSERT INTO friendships (requester_id, receiver_id, status)
             VALUES (:requester_id, :receiver_id, :status)'
        );

        return $statement->execute([
            'requester_id' => $requesterId,
            'receiver_id' => $receiverId,
            'status' => 'pending',
        ]);
    }

    public function accept(int $friendshipId, int $userId): bool
    {
        $statement = $this->pdo->prepare(
            "UPDATE friendships
             SET status = 'accepted'
             WHERE id = :id AND receiver_id = :user_id AND status = 'pending'"
        );
        $statement->execute(['id' => $friendshipId, 'user_id' => $userId]);

        return $statement->rowCount() > 0;
    }

    public function reject(int $friendshipId, int $userId): bool
    {
        $statement = $this->pdo->prepare(
            "UPDATE friendships
             SET status = 'rejected'
             WHERE id = :id AND receiver_id = :user_id AND status = 'pending'"
        );
        $statement->execute(['id' => $friendshipId, 'user_id' => $userId]);

        return $statement->rowCount() > 0;
    }

    public function getFriends(int $userId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT f.id, f.created_at, u.id AS user_id, u.name, u.email, up.training_level, up.goal
             FROM friendships f
             JOIN users u ON u.id = CASE WHEN f.requester_id = :user_id THEN f.receiver_id ELSE f.requester_id END
             LEFT JOIN user_profiles up ON up.user_id = u.id
             WHERE (f.requester_id = :user_id OR f.receiver_id = :user_id) AND f.status = 'accepted'
             ORDER BY u.name ASC"
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }

    public function getIncomingRequests(int $userId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT f.*, u.name, u.email
             FROM friendships f
             JOIN users u ON u.id = f.requester_id
             WHERE f.receiver_id = :user_id AND f.status = 'pending'
             ORDER BY f.created_at DESC"
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }

    public function getOutgoingRequests(int $userId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT f.*, u.name, u.email
             FROM friendships f
             JOIN users u ON u.id = f.receiver_id
             WHERE f.requester_id = :user_id AND f.status = 'pending'
             ORDER BY f.created_at DESC"
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }

    public function areFriendsOrPending(int $userA, int $userB): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT 1
             FROM friendships
             WHERE (requester_id = :user_a AND receiver_id = :user_b)
                OR (requester_id = :user_b AND receiver_id = :user_a)
             LIMIT 1'
        );
        $statement->execute([
            'user_a' => $userA,
            'user_b' => $userB,
        ]);

        return (bool) $statement->fetchColumn();
    }

    public function countFriends(int $userId): int
    {
        $statement = $this->pdo->prepare(
            "SELECT COUNT(*) FROM friendships
             WHERE (requester_id = :user_id OR receiver_id = :user_id) AND status = 'accepted'"
        );
        $statement->execute(['user_id' => $userId]);

        return (int) $statement->fetchColumn();
    }

    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM friendships WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $friendship = $statement->fetch();

        return $friendship ?: null;
    }

    public function friendUserIds(int $userId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT CASE WHEN requester_id = :user_id THEN receiver_id ELSE requester_id END AS friend_user_id
             FROM friendships
             WHERE (requester_id = :user_id OR receiver_id = :user_id) AND status = 'accepted'"
        );
        $statement->execute(['user_id' => $userId]);

        return array_map(static fn(array $row): int => (int) $row['friend_user_id'], $statement->fetchAll());
    }
}
