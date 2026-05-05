<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();
verify_csrf();

$friendshipId = (int) ($_GET['id'] ?? 0);
$repository = new FriendshipRepository(pdo());
$activityRepository = new ActivityLogRepository(mongo_db());
$notificationRepository = new NotificationRepository(mongo_db());
$friendship = $repository->findById($friendshipId);

if ($friendship && $repository->reject($friendshipId, current_user_id())) {
    $activityRepository->create(current_user_id(), 'rejected_friend_request', ['friendship_id' => $friendshipId]);
    $notificationRepository->create((int) $friendship['requester_id'], 'friendship_rejected', 'Zaproszenie odrzucone', current_user_name() . ' odrzucił Twoje zaproszenie.');
    redirect_with_flash('/friendships/index.php', 'success', 'Zaproszenie zostało odrzucone.');
}

redirect_with_flash('/friendships/index.php', 'warning', 'Nie udało się odrzucić zaproszenia.');
