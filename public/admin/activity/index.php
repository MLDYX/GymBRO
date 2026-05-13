<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/bootstrap.php';
require_admin();

$repository = new ActivityLogRepository(mongo_db());
$activities = $repository->all();

$pageTitle = 'Admin • Aktywnosc';
require_once base_path('app/includes/header.php');
?>
<?php if (!mongo_available()): ?>
    <div class="alert alert-warning">MongoDB nie jest teraz dostepne. Log aktywnosci jest chwilowo niedostepny.</div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="section-kicker mb-2">Admin</p>
        <h1 class="h3 mb-1">Activity log</h1>
        <p class="muted mb-0">Elastyczne logi aktywnosci calej aplikacji.</p>
    </div>
    <a href="<?= e(url('/admin/activity/create.php')) ?>" class="btn btn-primary">Dodaj aktywnosc</a>
</div>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>ID</th><th>User</th><th>Action</th><th>Created at</th><th>Akcje</th></tr></thead>
            <tbody>
            <?php foreach ($activities as $activity): ?>
                <tr>
                    <td><?= e(mongo_id_string($activity) ?? '-') ?></td>
                    <td>#<?= e((string) admin_value($activity, 'user_id')) ?></td>
                    <td><?= e((string) admin_value($activity, 'action')) ?></td>
                    <td><?= e((string) admin_value($activity, 'created_at')) ?></td>
                    <td class="d-flex gap-2">
                        <a class="btn btn-outline-primary btn-sm" href="<?= e(url('/admin/activity/edit.php?id=' . (string) mongo_id_string($activity))) ?>">Edytuj</a>
                        <form method="post" action="<?= e(url('/admin/activity/delete.php?id=' . (string) mongo_id_string($activity))) ?>">
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
