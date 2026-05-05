<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();

$repository = new GymRepository(pdo());
$gym = $repository->find((int) ($_GET['id'] ?? 0));

if (!$gym || (int) $gym['user_id'] !== current_user_id()) {
    redirect_with_flash('/gyms/index.php', 'danger', 'Nie możesz edytować tej siłowni.');
}

$errors = [];
if (is_post()) {
    verify_csrf();
    $errors = validate_required($_POST, ['name' => 'Nazwa', 'city' => 'Miasto']);
    if (!$errors && $repository->update((int) $gym['id'], current_user_id(), $_POST)) {
        redirect_with_flash('/gyms/index.php', 'success', 'Siłownia została zaktualizowana.');
    }
}

$pageTitle = 'Edytuj silownie';
require_once base_path('app/includes/header.php');
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card p-4">
            <h1 class="h3 mb-3">Edytuj siłownię</h1>
            <form method="post">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Nazwa</label><input type="text" name="name" class="form-control" value="<?= e($gym['name']) ?>"></div>
                    <div class="col-md-6"><label class="form-label">Miasto</label><input type="text" name="city" class="form-control" value="<?= e($gym['city']) ?>"></div>
                    <div class="col-12"><label class="form-label">Adres</label><input type="text" name="address" class="form-control" value="<?= e($gym['address'] ?? '') ?>"></div>
                    <div class="col-12"><label class="form-label">Opis</label><textarea name="description" class="form-control" rows="4"><?= e($gym['description'] ?? '') ?></textarea></div>
                </div>
                <button class="btn btn-primary mt-4">Zapisz zmiany</button>
            </form>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
