<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();
verify_csrf();

$eventId = (int) ($_GET['id'] ?? 0);
$eventRepository = new EventRepository(pdo());
$activityRepository = new ActivityLogRepository(mongo_db());

if ($eventRepository->leave($eventId, current_user_id())) {
    $activityRepository->create(current_user_id(), 'left_event', ['event_id' => $eventId]);
    redirect_with_flash('/events/show.php?id=' . $eventId, 'success', 'Opuszczono wydarzenie.');
}

redirect_with_flash('/events/show.php?id=' . $eventId, 'warning', 'Nie udało się opuścić wydarzenia.');
