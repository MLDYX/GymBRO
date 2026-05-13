<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/bootstrap.php';
require_admin();

$repository = new ExerciseRepository(pdo());
$userRepository = new UserRepository(pdo());
$users = $userRepository->all();
$errors = [];

if (is_post()) {
    verify_csrf();
    $errors = validate_required($_POST, [
        'user_id' => 'Autor',
        'name' => 'Nazwa',
        'muscle_group' => 'Partia miesniowa',
    ]);

    if (!$errors) {
        $repository->create((int) $_POST['user_id'], $_POST);
        redirect_with_flash('/admin/exercises/index.php', 'success', 'Cwiczenie zostalo dodane.');
    }
}

$pageTitle = 'Admin • Dodaj cwiczenie';
require_once base_path('app/includes/header.php');
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card p-4">
            <h1 class="h3 mb-3">Dodaj cwiczenie</h1>
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
                    <div class="col-md-6"><label class="form-label">Nazwa</label><input type="text" name="name" class="form-control" value="<?= old('name') ?>"></div>
                    <div class="col-md-6"><label class="form-label">Partia miesniowa</label><input type="text" name="muscle_group" class="form-control" value="<?= old('muscle_group') ?>"></div>
                    <div class="col-md-6"><label class="form-label">Sprzet</label><input type="text" name="equipment" class="form-control" value="<?= old('equipment') ?>"></div>
                    <div class="col-12"><label class="form-label">Opis</label><textarea name="description" rows="4" class="form-control"><?= old('description') ?></textarea></div>
                </div>
                <button class="btn btn-primary mt-4">Dodaj cwiczenie</button>
            </form>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
