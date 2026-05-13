<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/bootstrap.php';
require_admin();

$repository = new ProgressRepository(mongo_db());
$measurements = $repository->all();

$pageTitle = 'Admin • Progres';
require_once base_path('app/includes/header.php');
?>
<?php if (!mongo_available()): ?>
    <div class="alert alert-warning">MongoDB nie jest teraz dostepne. Pomiary progresu sa chwilowo niedostepne.</div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="section-kicker mb-2">Admin</p>
        <h1 class="h3 mb-1">Pomiary progresu</h1>
        <p class="muted mb-0">Globalny CRUD dokumentow progresu.</p>
    </div>
    <a href="<?= e(url('/admin/progress/create.php')) ?>" class="btn btn-primary">Dodaj pomiar</a>
</div>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>ID</th><th>User</th><th>Data</th><th>Typ</th><th>Wartosc</th><th>Akcje</th></tr></thead>
            <tbody>
            <?php foreach ($measurements as $measurement): ?>
                <tr>
                    <td><?= e(mongo_id_string($measurement) ?? '-') ?></td>
                    <td>#<?= e((string) admin_value($measurement, 'user_id')) ?></td>
                    <td><?= e((string) admin_value($measurement, 'date')) ?></td>
                    <td><?= e((string) admin_value($measurement, 'type')) ?></td>
                    <td><?= e((string) admin_value($measurement, 'value')) ?> <?= e((string) admin_value($measurement, 'unit')) ?></td>
                    <td class="d-flex gap-2">
                        <a class="btn btn-outline-primary btn-sm" href="<?= e(url('/admin/progress/edit.php?id=' . (string) mongo_id_string($measurement))) ?>">Edytuj</a>
                        <form method="post" action="<?= e(url('/admin/progress/delete.php?id=' . (string) mongo_id_string($measurement))) ?>">
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
