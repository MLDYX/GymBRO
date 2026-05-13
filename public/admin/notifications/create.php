<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/bootstrap.php';
require_admin();

$repository = new NotificationRepository(mongo_db());
$userRepository = new UserRepository(pdo());
$users = $userRepository->all();

if (is_post()) {
    verify_csrf();
    $created = $repository->createAdmin([
        'user_id' => (int) ($_POST['user_id'] ?? 0),
        'type' => trim((string) ($_POST['type'] ?? 'manual')),
        'title' => trim((string) ($_POST['title'] ?? '')),
        'content' => trim((string) ($_POST['content'] ?? '')),
        'is_read' => admin_bool_value($_POST['is_read'] ?? false),
        'created_at' => str_replace('T', ' ', (string) ($_POST['created_at'] ?? now_string())),
    ]);
    redirect_with_flash(
        '/admin/notifications/index.php',
        $created ? 'success' : 'warning',
        $created ? 'Powiadomienie zostalo dodane.' : 'Nie udalo sie dodac powiadomienia.'
    );
}

$pageTitle = 'Admin • Dodaj powiadomienie';
require_once base_path('app/includes/header.php');
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card p-4">
            <h1 class="h3 mb-3">Dodaj powiadomienie</h1>
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
                    <div class="col-md-6"><label class="form-label">Typ</label><input type="text" name="type" class="form-control" value="<?= old('type', 'manual') ?>"></div>
                    <div class="col-md-8"><label class="form-label">Tytul</label><input type="text" name="title" class="form-control" value="<?= old('title') ?>"></div>
                    <div class="col-md-4">
                        <label class="form-label">Przeczytane</label>
                        <select name="is_read" class="form-select">
                            <option value="0">nie</option>
                            <option value="1">tak</option>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label">Created at</label><input type="datetime-local" name="created_at" class="form-control" value="<?= e(admin_format_datetime_local(now_string())) ?>"></div>
                    <div class="col-12"><label class="form-label">Tresc</label><textarea name="content" rows="5" class="form-control"><?= old('content') ?></textarea></div>
                </div>
                <button class="btn btn-primary mt-4">Dodaj powiadomienie</button>
            </form>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
