<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/bootstrap.php';
require_admin();
verify_csrf();

$repository = new TrainingPlanRepository(pdo());

if ($repository->deleteAdmin((int) ($_GET['id'] ?? 0))) {
    redirect_with_flash('/admin/plans/index.php', 'success', 'Plan zostal usuniety.');
}

redirect_with_flash('/admin/plans/index.php', 'warning', 'Nie udalo sie usunac planu.');
