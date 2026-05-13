<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/bootstrap.php';
require_admin();
verify_csrf();

$repository = new ExerciseRepository(pdo());

if ($repository->deleteAdmin((int) ($_GET['id'] ?? 0))) {
    redirect_with_flash('/admin/exercises/index.php', 'success', 'Cwiczenie zostalo usuniete.');
}

redirect_with_flash('/admin/exercises/index.php', 'warning', 'Nie udalo sie usunac cwiczenia.');
