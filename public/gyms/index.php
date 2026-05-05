<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();

$repository = new GymRepository(pdo());
$gyms = $repository->all();

$pageTitle = 'Silownie';
require_once base_path('app/includes/header.php');
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Siłownie</h1>
        <p class="muted mb-0">Baza miejsc dodanych przez społeczność.</p>
    </div>
    <a href="/gyms/create.php" class="btn btn-primary">Dodaj siłownię</a>
</div>
<div class="card p-4">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
            <tr>
                <th>Nazwa</th>
                <th>Miasto</th>
                <th>Adres</th>
                <th>Dodał</th>
                <th>Akcje</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($gyms as $gym): ?>
                <tr>
                    <td><?= e($gym['name']) ?></td>
                    <td><?= e($gym['city']) ?></td>
                    <td><?= e($gym['address'] ?? '-') ?></td>
                    <td><?= e($gym['owner_name'] ?? 'Użytkownik usunięty') ?></td>
                    <td class="d-flex gap-2">
                        <?php if ((int) $gym['user_id'] === current_user_id()): ?>
                            <a class="btn btn-outline-primary btn-sm" href="/gyms/edit.php?id=<?= e((string) $gym['id']) ?>">Edytuj</a>
                            <form method="post" action="/gyms/delete.php?id=<?= e((string) $gym['id']) ?>">
                                <?= csrf_field() ?>
                                <button class="btn btn-outline-danger btn-sm">Usuń</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
