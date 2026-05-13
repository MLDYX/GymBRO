<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/bootstrap.php';
require_admin();

$repository = new ProgressRepository(mongo_db());
$userRepository = new UserRepository(pdo());
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
    $created = $repository->createAdmin([
        'user_id' => (int) ($_POST['user_id'] ?? 0),
        'date' => $_POST['date'] ?? '',
        'type' => $_POST['type'] ?? '',
        'value' => (float) ($_POST['value'] ?? 0),
        'unit' => trim((string) ($_POST['unit'] ?? '')),
        'note' => trim((string) ($_POST['note'] ?? '')),
        'created_at' => str_replace('T', ' ', (string) ($_POST['created_at'] ?? now_string())),
    ]);
    redirect_with_flash(
        '/admin/progress/index.php',
        $created ? 'success' : 'warning',
        $created ? 'Pomiar zostal dodany.' : 'Nie udalo sie dodac pomiaru.'
    );
}

$pageTitle = 'Admin • Dodaj pomiar';
require_once base_path('app/includes/header.php');
?>
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card p-4">
            <h1 class="h3 mb-3">Dodaj pomiar progresu</h1>
            <form method="post">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Uzytkownik</label>
                        <select name="user_id" class="form-select">
                            <?php foreach ($users as $user): ?>
                                <option value="<?= e((string) $user['id']) ?>"><?= e($user['name'] . ' • ' . $user['email']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label">Data</label><input type="date" name="date" class="form-control" value="<?= old('date', date('Y-m-d')) ?>"></div>
                    <div class="col-md-6">
                        <label class="form-label">Typ</label>
                        <select name="type" class="form-select">
                            <?php foreach ($types as $type): ?>
                                <option value="<?= e($type) ?>"><?= e($type) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3"><label class="form-label">Wartosc</label><input type="number" step="0.01" name="value" class="form-control" value="<?= old('value') ?>"></div>
                    <div class="col-md-3"><label class="form-label">Jednostka</label><input type="text" name="unit" class="form-control" value="<?= old('unit', 'kg') ?>"></div>
                    <div class="col-md-6"><label class="form-label">Created at</label><input type="datetime-local" name="created_at" class="form-control" value="<?= e(admin_format_datetime_local(now_string())) ?>"></div>
                    <div class="col-12"><label class="form-label">Notatka</label><textarea name="note" rows="4" class="form-control"><?= old('note') ?></textarea></div>
                </div>
                <button class="btn btn-primary mt-4">Dodaj pomiar</button>
            </form>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
