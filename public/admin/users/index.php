<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/bootstrap.php';
require_admin();

$userRepository = new UserRepository(pdo());
$users = $userRepository->all();

$pageTitle = 'Admin • Uzytkownicy';
require_once base_path('app/includes/header.php');
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="section-kicker mb-2">Admin</p>
        <h1 class="h3 mb-1">Uzytkownicy</h1>
        <p class="muted mb-0">Globalny podglad i edycja kont oraz profili.</p>
    </div>
</div>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
            <tr>
                <th>ID</th>
                <th>Imie i nazwisko</th>
                <th>E-mail</th>
                <th>Poziom</th>
                <th>Cel</th>
                <th>Onboarding</th>
                <th>Akcje</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= e((string) $user['id']) ?></td>
                    <td><?= e($user['name']) ?></td>
                    <td><?= e($user['email']) ?></td>
                    <td><?= e($user['training_level'] ?? '-') ?></td>
                    <td><?= e($user['goal'] ?? '-') ?></td>
                    <td><?= e(($user['onboarding_completed'] ?? false) ? 'tak' : 'nie') ?></td>
                    <td class="d-flex gap-2">
                        <a class="btn btn-outline-primary btn-sm" href="<?= e(url('/admin/users/edit.php?id=' . (string) $user['id'])) ?>">Edytuj</a>
                        <?php if (mb_strtolower((string) $user['email']) !== admin_email()): ?>
                            <form method="post" action="<?= e(url('/admin/users/delete.php?id=' . (string) $user['id'])) ?>">
                                <?= csrf_field() ?>
                                <button class="btn btn-outline-danger btn-sm">Usun</button>
                            </form>
                        <?php else: ?>
                            <span class="badge text-bg-light align-self-center">Admin</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
