<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();
verify_csrf();

$eventId = (int) ($_GET['id'] ?? 0);
$eventRepository = new EventRepository(pdo());
$notificationRepository = new NotificationRepository(mongo_db());
$activityRepository = new ActivityLogRepository(mongo_db());
$event = $eventRepository->find($eventId);

if (!$event) {
    redirect_with_flash('/events/index.php', 'danger', 'Nie znaleziono wydarzenia.');
}

if ($eventRepository->join($eventId, current_user_id())) {
    if ((int) $event['creator_id'] !== current_user_id()) {
        $notificationRepository->create((int) $event['creator_id'], 'event_joined', 'Nowy uczestnik treningu', current_user_name() . ' dołączył do Twojego treningu.');
    }
    $activityRepository->create(current_user_id(), 'joined_event', ['event_id' => $eventId, 'title' => $event['title']]);
    redirect_with_flash('/events/show.php?id=' . $eventId, 'success', 'Dołączono do wydarzenia.');
}

redirect_with_flash('/events/show.php?id=' . $eventId, 'warning', 'Nie udało się dołączyć do wydarzenia.');
