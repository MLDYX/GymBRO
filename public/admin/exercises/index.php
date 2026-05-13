<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/bootstrap.php';
require_admin();

$repository = new ExerciseRepository(pdo());
$exercises = $repository->all();

$pageTitle = 'Admin • Cwiczenia';
require_once base_path('app/includes/header.php');
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="section-kicker mb-2">Admin</p>
        <h1 class="h3 mb-1">Cwiczenia</h1>
        <p class="muted mb-0">Globalna baza cwiczen z pelnym CRUD.</p>
    </div>
    <a href="<?= e(url('/admin/exercises/create.php')) ?>" class="btn btn-primary">Dodaj cwiczenie</a>
</div>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>ID</th><th>Nazwa</th><th>Partia</th><th>Sprzet</th><th>Autor</th><th>Akcje</th></tr></thead>
            <tbody>
            <?php foreach ($exercises as $exercise): ?>
                <tr>
                    <td><?= e((string) $exercise['id']) ?></td>
                    <td><?= e($exercise['name']) ?></td>
                    <td><?= e($exercise['muscle_group']) ?></td>
                    <td><?= e($exercise['equipment'] ?? '-') ?></td>
                    <td><?= e($exercise['owner_name'] ?? 'Brak') ?></td>
                    <td class="d-flex gap-2">
                        <a class="btn btn-outline-primary btn-sm" href="<?= e(url('/admin/exercises/edit.php?id=' . (string) $exercise['id'])) ?>">Edytuj</a>
                        <form method="post" action="<?= e(url('/admin/exercises/delete.php?id=' . (string) $exercise['id'])) ?>">
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
