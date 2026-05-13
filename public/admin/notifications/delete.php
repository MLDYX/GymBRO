<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/bootstrap.php';
require_admin();
verify_csrf();

$repository = new NotificationRepository(mongo_db());

if ($repository->deleteAdmin((string) ($_GET['id'] ?? ''))) {
    redirect_with_flash('/admin/notifications/index.php', 'success', 'Powiadomienie zostalo usuniete.');
}

redirect_with_flash('/admin/notifications/index.php', 'warning', 'Nie udalo sie usunac powiadomienia.');
