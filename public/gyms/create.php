<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();

$repository = new GymRepository(pdo());
$errors = [];

if (is_post()) {
    verify_csrf();
    $errors = validate_required($_POST, ['name' => 'Nazwa', 'city' => 'Miasto']);
    if (!$errors) {
        $repository->create(current_user_id(), $_POST);
        redirect_with_flash('/gyms/index.php', 'success', 'Siłownia została dodana.');
    }
}

$pageTitle = 'Dodaj silownie';
require_once base_path('app/includes/header.php');
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card p-4">
            <h1 class="h3 mb-3">Dodaj siłownię</h1>
            <form method="post">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nazwa</label>
                        <input type="text" name="name" class="form-control" value="<?= old('name') ?>">
                        <?php if (isset($errors['name'])): ?><small class="text-danger"><?= e($errors['name']) ?></small><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Miasto</label>
                        <input type="text" name="city" class="form-control" value="<?= old('city') ?>">
                        <?php if (isset($errors['city'])): ?><small class="text-danger"><?= e($errors['city']) ?></small><?php endif; ?>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Adres</label>
                        <input type="text" name="address" class="form-control" value="<?= old('address') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Opis</label>
                        <textarea name="description" class="form-control" rows="4"><?= old('description') ?></textarea>
                    </div>
                </div>
                <button class="btn btn-primary mt-4">Zapisz</button>
            </form>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
