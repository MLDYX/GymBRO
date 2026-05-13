<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/bootstrap.php';
require_admin();

$measurementId = (string) ($_GET['id'] ?? '');
$repository = new ProgressRepository(mongo_db());
$userRepository = new UserRepository(pdo());
$measurement = $repository->findAdmin($measurementId);

if (!$measurement) {
    redirect_with_flash('/admin/progress/index.php', 'danger', 'Nie znaleziono pomiaru.');
}

$users = $userRepository->all();
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
    if ($repository->updateAdmin($measurementId, [
        'user_id' => (int) ($_POST['user_id'] ?? 0),
        'date' => $_POST['date'] ?? '',
        'type' => $_POST['type'] ?? '',
        'value' => (float) ($_POST['value'] ?? 0),
        'unit' => trim((string) ($_POST['unit'] ?? '')),
        'note' => trim((string) ($_POST['note'] ?? '')),
        'created_at' => str_replace('T', ' ', (string) ($_POST['created_at'] ?? now_string())),
    ])) {
        redirect_with_flash('/admin/progress/index.php', 'success', 'Pomiar zostal zaktualizowany.');
    }
}

$pageTitle = 'Admin • Edytuj pomiar';
require_once base_path('app/includes/header.php');
?>
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card p-4">
            <h1 class="h3 mb-3">Edytuj pomiar progresu</h1>
            <form method="post">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Uzytkownik</label>
                        <select name="user_id" class="form-select">
                            <?php foreach ($users as $user): ?>
                                <option value="<?= e((string) $user['id']) ?>" <?= (int) admin_value($measurement, 'user_id') === (int) $user['id'] ? 'selected' : '' ?>><?= e($user['name'] . ' • ' . $user['email']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label">Data</label><input type="date" name="date" class="form-control" value="<?= e((string) admin_value($measurement, 'date')) ?>"></div>
                    <div class="col-md-6">
                        <label class="form-label">Typ</label>
                        <select name="type" class="form-select">
                            <?php foreach ($types as $type): ?>
                                <option value="<?= e($type) ?>" <?= (string) admin_value($measurement, 'type') === $type ? 'selected' : '' ?>><?= e($type) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3"><label class="form-label">Wartosc</label><input type="number" step="0.01" name="value" class="form-control" value="<?= e((string) admin_value($measurement, 'value')) ?>"></div>
                    <div class="col-md-3"><label class="form-label">Jednostka</label><input type="text" name="unit" class="form-control" value="<?= e((string) admin_value($measurement, 'unit')) ?>"></div>
                    <div class="col-md-6"><label class="form-label">Created at</label><input type="datetime-local" name="created_at" class="form-control" value="<?= e(admin_format_datetime_local((string) admin_value($measurement, 'created_at'))) ?>"></div>
                    <div class="col-12"><label class="form-label">Notatka</label><textarea name="note" rows="4" class="form-control"><?= e((string) admin_value($measurement, 'note')) ?></textarea></div>
                </div>
                <button class="btn btn-primary mt-4">Zapisz pomiar</button>
            </form>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
