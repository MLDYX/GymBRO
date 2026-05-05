<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();
verify_csrf();

$planId = (int) ($_GET['plan_id'] ?? 0);
$repository = new TrainingPlanRepository(pdo());
if ($repository->deleteExercise((int) ($_GET['id'] ?? 0), current_user_id())) {
    redirect_with_flash('/plans/show.php?id=' . $planId, 'success', 'Ćwiczenie zostało usunięte z planu.');
}

redirect_with_flash('/plans/show.php?id=' . $planId, 'warning', 'Nie udało się usunąć ćwiczenia z planu.');
