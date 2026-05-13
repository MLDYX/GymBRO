<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/bootstrap.php';
require_admin();
verify_csrf();

$repository = new EventRepository(pdo());

if ($repository->deleteAdmin((int) ($_GET['id'] ?? 0))) {
    redirect_with_flash('/admin/events/index.php', 'success', 'Wydarzenie zostalo usuniete.');
}

redirect_with_flash('/admin/events/index.php', 'warning', 'Nie udalo sie usunac wydarzenia.');
