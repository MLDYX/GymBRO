<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/bootstrap.php';
require_admin();

$repository = new TrainingPlanRepository(pdo());
$plans = $repository->allAdmin();

$pageTitle = 'Admin • Plany';
require_once base_path('app/includes/header.php');
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="section-kicker mb-2">Admin</p>
        <h1 class="h3 mb-1">Plany treningowe</h1>
        <p class="muted mb-0">Pelny CRUD dla planow i ich struktury.</p>
    </div>
    <a href="<?= e(url('/admin/plans/create.php')) ?>" class="btn btn-primary">Dodaj plan</a>
</div>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>ID</th><th>Nazwa</th><th>Autor</th><th>Widocznosc</th><th>Dni</th><th>Akcje</th></tr></thead>
            <tbody>
            <?php foreach ($plans as $plan): ?>
                <tr>
                    <td><?= e((string) $plan['id']) ?></td>
                    <td><?= e($plan['name']) ?></td>
                    <td><?= e($plan['author_name']) ?></td>
                    <td><?= e($plan['visibility']) ?></td>
                    <td><?= e((string) $plan['day_count']) ?></td>
                    <td class="d-flex gap-2">
                        <a class="btn btn-outline-primary btn-sm" href="<?= e(url('/admin/plans/edit.php?id=' . (string) $plan['id'])) ?>">Edytuj</a>
                        <form method="post" action="<?= e(url('/admin/plans/delete.php?id=' . (string) $plan['id'])) ?>">
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
