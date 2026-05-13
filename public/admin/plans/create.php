<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/bootstrap.php';
require_admin();

$repository = new TrainingPlanRepository(pdo());
$userRepository = new UserRepository(pdo());
$users = $userRepository->all();
$errors = [];

if (is_post()) {
    verify_csrf();
    $errors = validate_required($_POST, [
        'user_id' => 'Autor',
        'name' => 'Nazwa',
    ]);

    if (!$errors) {
        $planId = $repository->create((int) $_POST['user_id'], $_POST);
        redirect_with_flash('/admin/plans/edit.php?id=' . $planId, 'success', 'Plan zostal utworzony.');
    }
}

$pageTitle = 'Admin • Dodaj plan';
require_once base_path('app/includes/header.php');
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card p-4">
            <h1 class="h3 mb-3">Dodaj plan treningowy</h1>
            <form method="post">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Autor</label>
                        <select name="user_id" class="form-select">
                            <option value="">Wybierz uzytkownika</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= e((string) $user['id']) ?>" <?= old('user_id') === (string) $user['id'] ? 'selected' : '' ?>><?= e($user['name'] . ' • ' . $user['email']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Widocznosc</label>
                        <select name="visibility" class="form-select">
                            <option value="private">private</option>
                            <option value="public">public</option>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label">Nazwa</label><input type="text" name="name" class="form-control" value="<?= old('name') ?>"></div>
                    <div class="col-md-6"><label class="form-label">Poziom</label><input type="text" name="level" class="form-control" value="<?= old('level') ?>"></div>
                    <div class="col-md-6"><label class="form-label">Cel</label><input type="text" name="goal" class="form-control" value="<?= old('goal') ?>"></div>
                    <div class="col-12"><label class="form-label">Opis</label><textarea name="description" rows="4" class="form-control"><?= old('description') ?></textarea></div>
                </div>
                <button class="btn btn-primary mt-4">Dodaj plan</button>
            </form>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
