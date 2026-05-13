<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/bootstrap.php';
require_admin();

$repository = new GymRepository(pdo());
$gyms = $repository->all();

$pageTitle = 'Admin • Silownie';
require_once base_path('app/includes/header.php');
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="section-kicker mb-2">Admin</p>
        <h1 class="h3 mb-1">Silownie</h1>
        <p class="muted mb-0">Pelne zarzadzanie miejscami treningowymi.</p>
    </div>
    <a href="<?= e(url('/admin/gyms/create.php')) ?>" class="btn btn-primary">Dodaj silownie</a>
</div>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>ID</th><th>Nazwa</th><th>Miasto</th><th>Adres</th><th>Wlasciciel</th><th>Akcje</th></tr></thead>
            <tbody>
            <?php foreach ($gyms as $gym): ?>
                <tr>
                    <td><?= e((string) $gym['id']) ?></td>
                    <td><?= e($gym['name']) ?></td>
                    <td><?= e($gym['city']) ?></td>
                    <td><?= e($gym['address'] ?? '-') ?></td>
                    <td><?= e($gym['owner_name'] ?? 'Brak') ?></td>
                    <td class="d-flex gap-2">
                        <a class="btn btn-outline-primary btn-sm" href="<?= e(url('/admin/gyms/edit.php?id=' . (string) $gym['id'])) ?>">Edytuj</a>
                        <form method="post" action="<?= e(url('/admin/gyms/delete.php?id=' . (string) $gym['id'])) ?>">
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
