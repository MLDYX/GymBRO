<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();

$repository = new WorkoutLogRepository(mongo_db());
$log = $repository->findOne((string) ($_GET['id'] ?? ''), current_user_id());

if (!$log) {
    redirect_with_flash('/logs/index.php', 'danger', 'Nie znaleziono wpisu treningowego.');
}

$pageTitle = 'Szczegoly wpisu';
require_once base_path('app/includes/header.php');
?>
<div class="row justify-content-center">
    <div class="col-xl-9">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">Trening z dnia <?= e((string) $log->training_date) ?></h1>
                    <div class="muted"><?= e((string) $log->duration_minutes) ?> min | nastrój: <?= e((string) $log->mood) ?></div>
                </div>
            </div>
            <p><?= e((string) $log->notes) ?></p>
            <?php foreach ($log->exercises as $exercise): ?>
                <div class="border rounded-4 p-3 mb-3">
                    <h2 class="h5"><?= e((string) $exercise['name']) ?></h2>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead><tr><th>Seria</th><th>Ciężar</th><th>Powtórzenia</th></tr></thead>
                            <tbody>
                            <?php foreach ($exercise['sets'] as $index => $set): ?>
                                <tr>
                                    <td><?= e((string) ($index + 1)) ?></td>
                                    <td><?= e((string) $set['weight']) ?> kg</td>
                                    <td><?= e((string) $set['reps']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
