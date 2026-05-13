<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/bootstrap.php';
require_admin();

$repository = new NotificationRepository(mongo_db());
$notifications = $repository->all();

$pageTitle = 'Admin • Powiadomienia';
require_once base_path('app/includes/header.php');
?>
<?php if (!mongo_available()): ?>
    <div class="alert alert-warning">MongoDB nie jest teraz dostepne. Powiadomienia sa chwilowo niedostepne.</div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="section-kicker mb-2">Admin</p>
        <h1 class="h3 mb-1">Powiadomienia</h1>
        <p class="muted mb-0">CRUD dokumentow notifications.</p>
    </div>
    <a href="<?= e(url('/admin/notifications/create.php')) ?>" class="btn btn-primary">Dodaj powiadomienie</a>
</div>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>ID</th><th>User</th><th>Tytul</th><th>Typ</th><th>Read</th><th>Akcje</th></tr></thead>
            <tbody>
            <?php foreach ($notifications as $notification): ?>
                <tr>
                    <td><?= e(mongo_id_string($notification) ?? '-') ?></td>
                    <td>#<?= e((string) admin_value($notification, 'user_id')) ?></td>
                    <td><?= e((string) admin_value($notification, 'title')) ?></td>
                    <td><?= e((string) admin_value($notification, 'type')) ?></td>
                    <td><?= e(admin_bool_value(admin_value($notification, 'is_read')) ? 'tak' : 'nie') ?></td>
                    <td class="d-flex gap-2">
                        <a class="btn btn-outline-primary btn-sm" href="<?= e(url('/admin/notifications/edit.php?id=' . (string) mongo_id_string($notification))) ?>">Edytuj</a>
                        <form method="post" action="<?= e(url('/admin/notifications/delete.php?id=' . (string) mongo_id_string($notification))) ?>">
                            <?= csrf_field() ?>
                            <button class="btn btn-outline-danger btn-sm">Usun</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
