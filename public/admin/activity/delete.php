<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/bootstrap.php';
require_admin();
verify_csrf();

$repository = new ActivityLogRepository(mongo_db());

if ($repository->deleteAdmin((string) ($_GET['id'] ?? ''))) {
    redirect_with_flash('/admin/activity/index.php', 'success', 'Wpis aktywnosci zostal usuniety.');
}

redirect_with_flash('/admin/activity/index.php', 'warning', 'Nie udalo sie usunac wpisu aktywnosci.');
