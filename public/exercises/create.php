<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();

$repository = new ExerciseRepository(pdo());
$errors = [];

if (is_post()) {
    verify_csrf();
    $errors = validate_required($_POST, ['name' => 'Nazwa', 'muscle_group' => 'Partia mięśniowa']);
    if (!$errors) {
        $repository->create(current_user_id(), $_POST);
        redirect_with_flash('/exercises/index.php', 'success', 'Ćwiczenie zostało dodane.');
    }
}

$pageTitle = 'Dodaj cwiczenie';
require_once base_path('app/includes/header.php');
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card p-4">
            <h1 class="h3 mb-3">Dodaj ćwiczenie</h1>
            <form method="post">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Nazwa</label><input type="text" name="name" class="form-control" value="<?= old('name') ?>"></div>
                    <div class="col-md-6"><label class="form-label">Partia mięśniowa</label><input type="text" name="muscle_group" class="form-control" value="<?= old('muscle_group') ?>"></div>
                    <div class="col-12"><label class="form-label">Sprzęt</label><input type="text" name="equipment" class="form-control" value="<?= old('equipment') ?>"></div>
                    <div class="col-12"><label class="form-label">Opis</label><textarea name="description" class="form-control" rows="4"><?= old('description') ?></textarea></div>
                </div>
                <button class="btn btn-primary mt-4">Zapisz</button>
            </form>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
