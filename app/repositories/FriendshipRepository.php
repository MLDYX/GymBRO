<?php

declare(strict_types=1);

class FriendshipRepository
{
    public function __construct(private mixed $connection = null)
    {
    }

    public function sendRequest(int $requesterId, int $receiverId): bool
    {
        return postgres_send_friend_request($requesterId, $receiverId);
    }

    public function accept(int $friendshipId, int $userId): bool
    {
        return postgres_accept_friend_request($friendshipId, $userId);
    }

    public function reject(int $friendshipId, int $userId): bool
    {
        return postgres_reject_friend_request($friendshipId, $userId);
    }

    public function getFriends(int $userId): array
    {
        return postgres_get_friends($userId);
    }

    public function getIncomingRequests(int $userId): array
    {
        return postgres_get_incoming_friend_requests($userId);
    }

    public function getOutgoingRequests(int $userId): array
    {
        return postgres_get_outgoing_friend_requests($userId);
    }

    public function areFriendsOrPending(int $userA, int $userB): bool
    {
        return postgres_are_friends_or_pending($userA, $userB);
    }

    public function countFriends(int $userId): int
    {
        return postgres_count_friends($userId);
    }

    public function findById(int $id): ?array
    {
        return postgres_find_friendship_by_id($id);
    }

    public function friendUserIds(int $userId): array
    {
        return postgres_get_friend_user_ids($userId);
    }

    public function countAll(): int
    {
        return postgres_count_friendships();
    }

    public function pending(int $limit): array
    {
        return postgres_get_pending_friend_requests_global($limit);
    }
}
