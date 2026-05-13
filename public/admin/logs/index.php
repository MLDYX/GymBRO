<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/bootstrap.php';
require_admin();

$repository = new WorkoutLogRepository(mongo_db());
$logs = $repository->all();

$pageTitle = 'Admin • Logi treningowe';
require_once base_path('app/includes/header.php');
?>
<?php if (!mongo_available()): ?>
    <div class="alert alert-warning">MongoDB nie jest teraz dostepne. Logi treningowe sa chwilowo niedostepne.</div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="section-kicker mb-2">Admin</p>
        <h1 class="h3 mb-1">Logi treningowe</h1>
        <p class="muted mb-0">Wpisy MongoDB z cwiczeniami i seriami.</p>
    </div>
</div>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>ID</th><th>User</th><th>Data</th><th>Czas</th><th>Nastroj</th><th>Liczba cwiczen</th><th>Akcje</th></tr></thead>
            <tbody>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= e(mongo_id_string($log) ?? '-') ?></td>
                    <td>#<?= e((string) admin_value($log, 'user_id')) ?></td>
                    <td><?= e((string) admin_value($log, 'training_date')) ?></td>
                    <td><?= e((string) admin_value($log, 'duration_minutes')) ?> min</td>
                    <td><?= e((string) admin_value($log, 'mood')) ?></td>
                    <td><?= e((string) count((array) admin_value($log, 'exercises', []))) ?></td>
                    <td class="d-flex gap-2">
                        <a class="btn btn-outline-primary btn-sm" href="<?= e(url('/admin/logs/edit.php?id=' . (string) mongo_id_string($log))) ?>">Edytuj</a>
                        <form method="post" action="<?= e(url('/admin/logs/delete.php?id=' . (string) mongo_id_string($log))) ?>">
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
