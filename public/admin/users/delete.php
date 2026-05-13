<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/bootstrap.php';
require_admin();
verify_csrf();

$userId = (int) ($_GET['id'] ?? 0);
$userRepository = new UserRepository(pdo());
$user = $userRepository->findById($userId);

if (!$user) {
    redirect_with_flash('/admin/users/index.php', 'danger', 'Nie znaleziono uzytkownika.');
}

if ($userId === current_user_id() || mb_strtolower((string) $user['email']) === admin_email()) {
    redirect_with_flash('/admin/users/index.php', 'warning', 'Nie mozna usunac aktywnego konta administratora.');
}

if ($userRepository->delete($userId)) {
    redirect_with_flash('/admin/users/index.php', 'success', 'Uzytkownik zostal usuniety.');
}

redirect_with_flash('/admin/users/index.php', 'warning', 'Nie udalo sie usunac uzytkownika.');
