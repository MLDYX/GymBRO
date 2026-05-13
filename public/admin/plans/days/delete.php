<?php

declare(strict_types=1);

require_once dirname(__DIR__, 4) . '/app/bootstrap.php';
require_admin();
verify_csrf();

$dayId = (int) ($_GET['id'] ?? 0);
$planId = (int) ($_GET['plan_id'] ?? 0);
$repository = new TrainingPlanRepository(pdo());

if ($repository->deleteDayAdmin($dayId)) {
    redirect_with_flash('/admin/plans/edit.php?id=' . $planId, 'success', 'Dzien zostal usuniety.');
}

redirect_with_flash('/admin/plans/edit.php?id=' . $planId, 'warning', 'Nie udalo sie usunac dnia.');
