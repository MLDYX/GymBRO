<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();

if (is_post()) {
    verify_csrf();
}

$repository = new GymRepository(pdo());
if ($repository->delete((int) ($_GET['id'] ?? 0), current_user_id())) {
    redirect_with_flash('/gyms/index.php', 'success', 'Siłownia została usunięta.');
}

redirect_with_flash('/gyms/index.php', 'warning', 'Nie udało się usunąć siłowni.');
