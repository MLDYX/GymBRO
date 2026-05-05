<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();
verify_csrf();

$receiverId = (int) ($_GET['id'] ?? 0);
$repository = new FriendshipRepository(pdo());
$userRepository = new UserRepository(pdo());
$notificationRepository = new NotificationRepository(mongo_db());
$activityRepository = new ActivityLogRepository(mongo_db());

$receiver = $userRepository->findById($receiverId);
if (!$receiver) {
    redirect_with_flash('/users/index.php', 'danger', 'Nie znaleziono użytkownika.');
}

if ($repository->sendRequest(current_user_id(), $receiverId)) {
    $notificationRepository->create($receiverId, 'friend_request', 'Nowe zaproszenie do znajomych', current_user_name() . ' wysłał Ci zaproszenie.');
    $activityRepository->create(current_user_id(), 'sent_friend_request', ['receiver_id' => $receiverId, 'receiver_name' => $receiver['name']]);
    redirect_with_flash('/friendships/index.php', 'success', 'Zaproszenie zostało wysłane.');
}

redirect_with_flash('/users/index.php', 'warning', 'Nie można wysłać zaproszenia.');
