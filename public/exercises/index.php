<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();

$repository = new ExerciseRepository(pdo());
$exercises = $repository->all();

$pageTitle = 'Cwiczenia';
require_once base_path('app/includes/header.php');
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Ćwiczenia</h1>
        <p class="muted mb-0">Twoja i społecznościowa baza ćwiczeń.</p>
    </div>
    <a href="/exercises/create.php" class="btn btn-primary">Dodaj ćwiczenie</a>
</div>
<div class="card p-4">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
            <tr>
                <th>Nazwa</th>
                <th>Partia mięśniowa</th>
                <th>Sprzęt</th>
                <th>Dodał</th>
                <th>Akcje</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($exercises as $exercise): ?>
                <tr>
                    <td><?= e($exercise['name']) ?></td>
                    <td><?= e($exercise['muscle_group']) ?></td>
                    <td><?= e($exercise['equipment'] ?? '-') ?></td>
                    <td><?= e($exercise['owner_name'] ?? 'Użytkownik usunięty') ?></td>
                    <td class="d-flex gap-2">
                        <?php if ((int) $exercise['user_id'] === current_user_id()): ?>
                            <a class="btn btn-outline-primary btn-sm" href="/exercises/edit.php?id=<?= e((string) $exercise['id']) ?>">Edytuj</a>
                            <form method="post" action="/exercises/delete.php?id=<?= e((string) $exercise['id']) ?>">
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
