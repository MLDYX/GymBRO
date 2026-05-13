<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/bootstrap.php';
require_admin();

$commentId = (string) ($_GET['id'] ?? '');
$repository = new CommentRepository(mongo_db());
$userRepository = new UserRepository(pdo());
$planRepository = new TrainingPlanRepository(pdo());
$comment = $repository->findAdmin($commentId);

if (!$comment) {
    redirect_with_flash('/admin/comments/index.php', 'danger', 'Nie znaleziono komentarza.');
}

$users = $userRepository->all();
$plans = $planRepository->allAdmin();

if (is_post()) {
    verify_csrf();
    if ($repository->updateAdmin($commentId, [
        'plan_id' => (int) ($_POST['plan_id'] ?? 0),
        'user_id' => (int) ($_POST['user_id'] ?? 0),
        'user_name' => trim((string) ($_POST['user_name'] ?? '')),
        'content' => trim((string) ($_POST['content'] ?? '')),
        'rating' => max(1, min(5, (int) ($_POST['rating'] ?? 5))),
        'created_at' => str_replace('T', ' ', (string) ($_POST['created_at'] ?? now_string())),
    ])) {
        redirect_with_flash('/admin/comments/index.php', 'success', 'Komentarz zostal zaktualizowany.');
    }
}

$pageTitle = 'Admin • Edytuj komentarz';
require_once base_path('app/includes/header.php');
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card p-4">
            <h1 class="h3 mb-3">Edytuj komentarz</h1>
            <form method="post">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Plan</label>
                        <select name="plan_id" class="form-select">
                            <?php foreach ($plans as $plan): ?>
                                <option value="<?= e((string) $plan['id']) ?>" <?= (int) admin_value($comment, 'plan_id') === (int) $plan['id'] ? 'selected' : '' ?>><?= e($plan['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Uzytkownik</label>
                        <select name="user_id" class="form-select">
                            <?php foreach ($users as $user): ?>
                                <option value="<?= e((string) $user['id']) ?>" <?= (int) admin_value($comment, 'user_id') === (int) $user['id'] ? 'selected' : '' ?>><?= e($user['name'] . ' • ' . $user['email']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label">User name</label><input type="text" name="user_name" class="form-control" value="<?= e((string) admin_value($comment, 'user_name')) ?>"></div>
                    <div class="col-md-3"><label class="form-label">Ocena</label><input type="number" min="1" max="5" name="rating" class="form-control" value="<?= e((string) admin_value($comment, 'rating')) ?>"></div>
                    <div class="col-md-3"><label class="form-label">Created at</label><input type="datetime-local" name="created_at" class="form-control" value="<?= e(admin_format_datetime_local((string) admin_value($comment, 'created_at'))) ?>"></div>
                    <div class="col-12"><label class="form-label">Komentarz</label><textarea name="content" rows="5" class="form-control"><?= e((string) admin_value($comment, 'content')) ?></textarea></div>
                </div>
                <button class="btn btn-primary mt-4">Zapisz komentarz</button>
            </form>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
