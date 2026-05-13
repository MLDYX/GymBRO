<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/bootstrap.php';
require_admin();

$repository = new EventRepository(pdo());
$events = $repository->allAdmin();

$pageTitle = 'Admin • Wydarzenia';
require_once base_path('app/includes/header.php');
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="section-kicker mb-2">Admin</p>
        <h1 class="h3 mb-1">Wydarzenia</h1>
        <p class="muted mb-0">Wspolne treningi z globalnym CRUD.</p>
    </div>
    <a href="<?= e(url('/admin/events/create.php')) ?>" class="btn btn-primary">Dodaj wydarzenie</a>
</div>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>ID</th><th>Tytul</th><th>Data</th><th>Status</th><th>Silownia</th><th>Tworca</th><th>Uczestnicy</th><th>Akcje</th></tr></thead>
            <tbody>
            <?php foreach ($events as $event): ?>
                <tr>
                    <td><?= e((string) $event['id']) ?></td>
                    <td><?= e($event['title']) ?></td>
                    <td><?= e($event['event_date']) ?> <?= e($event['start_time']) ?></td>
                    <td><?= e($event['status']) ?></td>
                    <td><?= e(($event['gym_name'] ?? 'Brak') . ' / ' . ($event['gym_city'] ?? '-')) ?></td>
                    <td><?= e($event['creator_name']) ?></td>
                    <td><?= e((string) $event['participants_count']) ?>/<?= e((string) $event['max_participants']) ?></td>
                    <td class="d-flex gap-2">
                        <a class="btn btn-outline-primary btn-sm" href="<?= e(url('/admin/events/edit.php?id=' . (string) $event['id'])) ?>">Edytuj</a>
                        <form method="post" action="<?= e(url('/admin/events/delete.php?id=' . (string) $event['id'])) ?>">
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
