<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/bootstrap.php';
require_admin();
verify_csrf();

$repository = new WorkoutLogRepository(mongo_db());

if ($repository->deleteAdmin((string) ($_GET['id'] ?? ''))) {
    redirect_with_flash('/admin/logs/index.php', 'success', 'Log treningowy zostal usuniety.');
}

redirect_with_flash('/admin/logs/index.php', 'warning', 'Nie udalo sie usunac logu treningowego.');
