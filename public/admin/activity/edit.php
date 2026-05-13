<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/bootstrap.php';
require_admin();

$activityId = (string) ($_GET['id'] ?? '');
$repository = new ActivityLogRepository(mongo_db());
$userRepository = new UserRepository(pdo());
$activity = $repository->findAdmin($activityId);

if (!$activity) {
    redirect_with_flash('/admin/activity/index.php', 'danger', 'Nie znaleziono wpisu aktywnosci.');
}

$users = $userRepository->all();
$errors = [];

if (is_post()) {
    verify_csrf();
    $details = admin_decode_json_object((string) ($_POST['details_json'] ?? '{}'), 'Details', $errors);

    if (!$errors && $repository->updateAdmin($activityId, [
        'user_id' => (int) ($_POST['user_id'] ?? 0),
        'action' => trim((string) ($_POST['action'] ?? '')),
        'details' => $details,
        'created_at' => str_replace('T', ' ', (string) ($_POST['created_at'] ?? now_string())),
    ])) {
        redirect_with_flash('/admin/activity/index.php', 'success', 'Wpis aktywnosci zostal zaktualizowany.');
    }
}

$pageTitle = 'Admin • Edytuj aktywnosc';
require_once base_path('app/includes/header.php');
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card p-4">
            <h1 class="h3 mb-3">Edytuj aktywnosc</h1>
            <?php foreach ($errors as $error): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
            <?php endforeach; ?>
            <form method="post">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Uzytkownik</label>
                        <select name="user_id" class="form-select">
                            <?php foreach ($users as $user): ?>
                                <option value="<?= e((string) $user['id']) ?>" <?= (int) admin_value($activity, 'user_id') === (int) $user['id'] ? 'selected' : '' ?>><?= e($user['name'] . ' • ' . $user['email']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label">Action</label><input type="text" name="action" class="form-control" value="<?= e((string) admin_value($activity, 'action')) ?>"></div>
                    <div class="col-md-6"><label class="form-label">Created at</label><input type="datetime-local" name="created_at" class="form-control" value="<?= e(admin_format_datetime_local((string) admin_value($activity, 'created_at'))) ?>"></div>
                    <div class="col-12"><label class="form-label">Details JSON</label><textarea name="details_json" rows="8" class="form-control"><?= e(admin_pretty_json(admin_value($activity, 'details', new stdClass()))) ?></textarea></div>
                </div>
                <button class="btn btn-primary mt-4">Zapisz aktywnosc</button>
            </form>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
