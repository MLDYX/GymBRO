<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();

$repository = new TrainingPlanRepository(pdo());
$activityRepository = new ActivityLogRepository(mongo_db());
$errors = [];

if (is_post()) {
    verify_csrf();
    $errors = validate_required($_POST, ['name' => 'Nazwa']);
    if (!$errors) {
        $planId = $repository->create(current_user_id(), $_POST);
        $activityRepository->create(current_user_id(), 'created_training_plan', ['plan_id' => $planId, 'plan_name' => $_POST['name']]);
        redirect_with_flash('/plans/show.php?id=' . $planId, 'success', 'Plan został utworzony.');
    }
}

$pageTitle = 'Nowy plan';
require_once base_path('app/includes/header.php');
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card p-4">
            <h1 class="h3 mb-3">Utwórz plan treningowy</h1>
            <form method="post">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-8"><label class="form-label">Nazwa</label><input type="text" name="name" class="form-control" value="<?= old('name') ?>"></div>
                    <div class="col-md-4"><label class="form-label">Widoczność</label><select name="visibility" class="form-select"><option value="private">Prywatny</option><option value="public">Publiczny</option></select></div>
                    <div class="col-md-6"><label class="form-label">Poziom</label><input type="text" name="level" class="form-control" value="<?= old('level') ?>"></div>
                    <div class="col-md-6"><label class="form-label">Cel</label><input type="text" name="goal" class="form-control" value="<?= old('goal') ?>"></div>
                    <div class="col-12"><label class="form-label">Opis</label><textarea name="description" rows="4" class="form-control"><?= old('description') ?></textarea></div>
                </div>
                <button class="btn btn-primary mt-4">Zapisz plan</button>
            </form>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
