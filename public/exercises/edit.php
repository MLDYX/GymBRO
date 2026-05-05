<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();

$repository = new ExerciseRepository(pdo());
$exercise = $repository->find((int) ($_GET['id'] ?? 0));

if (!$exercise || (int) $exercise['user_id'] !== current_user_id()) {
    redirect_with_flash('/exercises/index.php', 'danger', 'Nie możesz edytować tego ćwiczenia.');
}

if (is_post()) {
    verify_csrf();
    if ($repository->update((int) $exercise['id'], current_user_id(), $_POST)) {
        redirect_with_flash('/exercises/index.php', 'success', 'Ćwiczenie zostało zaktualizowane.');
    }
}

$pageTitle = 'Edytuj cwiczenie';
require_once base_path('app/includes/header.php');
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card p-4">
            <h1 class="h3 mb-3">Edytuj ćwiczenie</h1>
            <form method="post">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Nazwa</label><input type="text" name="name" class="form-control" value="<?= e($exercise['name']) ?>"></div>
                    <div class="col-md-6"><label class="form-label">Partia mięśniowa</label><input type="text" name="muscle_group" class="form-control" value="<?= e($exercise['muscle_group']) ?>"></div>
                    <div class="col-12"><label class="form-label">Sprzęt</label><input type="text" name="equipment" class="form-control" value="<?= e($exercise['equipment'] ?? '') ?>"></div>
                    <div class="col-12"><label class="form-label">Opis</label><textarea name="description" class="form-control" rows="4"><?= e($exercise['description'] ?? '') ?></textarea></div>
                </div>
                <button class="btn btn-primary mt-4">Zapisz zmiany</button>
            </form>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
