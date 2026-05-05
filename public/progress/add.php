<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();

$repository = new ProgressRepository(mongo_db());
$activityRepository = new ActivityLogRepository(mongo_db());
$types = [
    'body_weight',
    'bench_press',
    'squat',
    'deadlift',
    'arm_circumference',
    'chest_circumference',
    'waist_circumference',
];

if (is_post()) {
    verify_csrf();
    $repository->create([
        'user_id' => current_user_id(),
        'date' => $_POST['date'],
        'type' => $_POST['type'],
        'value' => (float) $_POST['value'],
        'unit' => trim((string) $_POST['unit']),
        'note' => trim((string) $_POST['note']),
        'created_at' => now_string(),
    ]);
    $activityRepository->create(current_user_id(), 'added_progress_measurement', ['type' => $_POST['type'], 'value' => (float) $_POST['value']]);
    redirect_with_flash('/progress/index.php?type=' . urlencode((string) $_POST['type']), 'success', 'Pomiar został dodany.');
}

$pageTitle = 'Dodaj pomiar';
require_once base_path('app/includes/header.php');
?>
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card p-4">
            <h1 class="h3 mb-3">Dodaj pomiar progresu</h1>
            <form method="post">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Data</label><input type="date" name="date" class="form-control" value="<?= old('date', date('Y-m-d')) ?>"></div>
                    <div class="col-md-6"><label class="form-label">Typ</label><select name="type" class="form-select"><?php foreach ($types as $type): ?><option value="<?= e($type) ?>"><?= e($type) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-6"><label class="form-label">Wartość</label><input type="number" step="0.01" name="value" class="form-control" value="<?= old('value') ?>"></div>
                    <div class="col-md-6"><label class="form-label">Jednostka</label><input type="text" name="unit" class="form-control" value="<?= old('unit', 'kg') ?>"></div>
                    <div class="col-12"><label class="form-label">Notatka</label><textarea name="note" rows="4" class="form-control"><?= old('note') ?></textarea></div>
                </div>
                <button class="btn btn-primary mt-4">Zapisz pomiar</button>
            </form>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
