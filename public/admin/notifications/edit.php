<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/bootstrap.php';
require_admin();

$notificationId = (string) ($_GET['id'] ?? '');
$repository = new NotificationRepository(mongo_db());
$userRepository = new UserRepository(pdo());
$notification = $repository->findAdmin($notificationId);

if (!$notification) {
    redirect_with_flash('/admin/notifications/index.php', 'danger', 'Nie znaleziono powiadomienia.');
}

$users = $userRepository->all();

if (is_post()) {
    verify_csrf();
    if ($repository->updateAdmin($notificationId, [
        'user_id' => (int) ($_POST['user_id'] ?? 0),
        'type' => trim((string) ($_POST['type'] ?? 'manual')),
        'title' => trim((string) ($_POST['title'] ?? '')),
        'content' => trim((string) ($_POST['content'] ?? '')),
        'is_read' => admin_bool_value($_POST['is_read'] ?? false),
        'created_at' => str_replace('T', ' ', (string) ($_POST['created_at'] ?? now_string())),
    ])) {
        redirect_with_flash('/admin/notifications/index.php', 'success', 'Powiadomienie zostalo zaktualizowane.');
    }
}

$pageTitle = 'Admin • Edytuj powiadomienie';
require_once base_path('app/includes/header.php');
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card p-4">
            <h1 class="h3 mb-3">Edytuj powiadomienie</h1>
            <form method="post">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Uzytkownik</label>
                        <select name="user_id" class="form-select">
                            <?php foreach ($users as $user): ?>
                                <option value="<?= e((string) $user['id']) ?>" <?= (int) admin_value($notification, 'user_id') === (int) $user['id'] ? 'selected' : '' ?>><?= e($user['name'] . ' • ' . $user['email']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label">Typ</label><input type="text" name="type" class="form-control" value="<?= e((string) admin_value($notification, 'type')) ?>"></div>
                    <div class="col-md-8"><label class="form-label">Tytul</label><input type="text" name="title" class="form-control" value="<?= e((string) admin_value($notification, 'title')) ?>"></div>
                    <div class="col-md-4">
                        <label class="form-label">Przeczytane</label>
                        <select name="is_read" class="form-select">
                            <option value="0" <?= !admin_bool_value(admin_value($notification, 'is_read')) ? 'selected' : '' ?>>nie</option>
                            <option value="1" <?= admin_bool_value(admin_value($notification, 'is_read')) ? 'selected' : '' ?>>tak</option>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label">Created at</label><input type="datetime-local" name="created_at" class="form-control" value="<?= e(admin_format_datetime_local((string) admin_value($notification, 'created_at'))) ?>"></div>
                    <div class="col-12"><label class="form-label">Tresc</label><textarea name="content" rows="5" class="form-control"><?= e((string) admin_value($notification, 'content')) ?></textarea></div>
                </div>
                <button class="btn btn-primary mt-4">Zapisz powiadomienie</button>
            </form>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
