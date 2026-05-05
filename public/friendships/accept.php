<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();
verify_csrf();

$friendshipId = (int) ($_GET['id'] ?? 0);
$repository = new FriendshipRepository(pdo());
$notificationRepository = new NotificationRepository(mongo_db());
$activityRepository = new ActivityLogRepository(mongo_db());
$friendship = $repository->findById($friendshipId);

if ($friendship && $repository->accept($friendshipId, current_user_id())) {
    $activityRepository->create(current_user_id(), 'accepted_friend_request', ['friendship_id' => $friendshipId]);
    $notificationRepository->create((int) $friendship['requester_id'], 'friendship_accepted', 'Zaproszenie zaakceptowane', current_user_name() . ' zaakceptował Twoje zaproszenie.');
    redirect_with_flash('/friendships/index.php', 'success', 'Zaproszenie zostało zaakceptowane.');
}

redirect_with_flash('/friendships/index.php', 'warning', 'Nie udało się zaakceptować zaproszenia.');
