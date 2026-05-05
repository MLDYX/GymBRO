<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();

$repository = new TrainingPlanRepository(pdo());
$plan = $repository->find((int) ($_GET['id'] ?? 0));

if (!$plan || (int) $plan['user_id'] !== current_user_id()) {
    redirect_with_flash('/plans/index.php', 'danger', 'Nie możesz edytować tego planu.');
}

if (is_post()) {
    verify_csrf();
    if ($repository->update((int) $plan['id'], current_user_id(), $_POST)) {
        redirect_with_flash('/plans/show.php?id=' . $plan['id'], 'success', 'Plan został zaktualizowany.');
    }
}

$pageTitle = 'Edytuj plan';
require_once base_path('app/includes/header.php');
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card p-4">
            <h1 class="h3 mb-3">Edytuj plan</h1>
            <form method="post">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-8"><label class="form-label">Nazwa</label><input type="text" name="name" class="form-control" value="<?= e($plan['name']) ?>"></div>
                    <div class="col-md-4"><label class="form-label">Widoczność</label><select name="visibility" class="form-select"><option value="private" <?= $plan['visibility'] === 'private' ? 'selected' : '' ?>>Prywatny</option><option value="public" <?= $plan['visibility'] === 'public' ? 'selected' : '' ?>>Publiczny</option></select></div>
                    <div class="col-md-6"><label class="form-label">Poziom</label><input type="text" name="level" class="form-control" value="<?= e($plan['level'] ?? '') ?>"></div>
                    <div class="col-md-6"><label class="form-label">Cel</label><input type="text" name="goal" class="form-control" value="<?= e($plan['goal'] ?? '') ?>"></div>
                    <div class="col-12"><label class="form-label">Opis</label><textarea name="description" rows="4" class="form-control"><?= e($plan['description'] ?? '') ?></textarea></div>
                </div>
                <button class="btn btn-primary mt-4">Zapisz zmiany</button>
            </form>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
