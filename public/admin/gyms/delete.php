<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/bootstrap.php';
require_admin();
verify_csrf();

$repository = new GymRepository(pdo());

if ($repository->deleteAdmin((int) ($_GET['id'] ?? 0))) {
    redirect_with_flash('/admin/gyms/index.php', 'success', 'Silownia zostala usunieta.');
}

redirect_with_flash('/admin/gyms/index.php', 'warning', 'Nie udalo sie usunac silowni.');
