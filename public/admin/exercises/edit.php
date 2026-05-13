<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/bootstrap.php';
require_admin();

$exerciseId = (int) ($_GET['id'] ?? 0);
$repository = new ExerciseRepository(pdo());
$userRepository = new UserRepository(pdo());
$exercise = $repository->find($exerciseId);

if (!$exercise) {
    redirect_with_flash('/admin/exercises/index.php', 'danger', 'Nie znaleziono cwiczenia.');
}

$users = $userRepository->all();

if (is_post()) {
    verify_csrf();
    if ($repository->updateAdmin($exerciseId, $_POST)) {
        redirect_with_flash('/admin/exercises/index.php', 'success', 'Cwiczenie zostalo zaktualizowane.');
    }
}

$pageTitle = 'Admin • Edytuj cwiczenie';
require_once base_path('app/includes/header.php');
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card p-4">
            <h1 class="h3 mb-3">Edytuj cwiczenie</h1>
            <form method="post">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Autor</label>
                        <select name="user_id" class="form-select">
                            <option value="">Bez autora</option>
                            <?php foreach ($users as $user): ?>
                                <?php $selected = (string) ($exercise['user_id'] ?? '') === (string) $user['id']; ?>
                                <option value="<?= e((string) $user['id']) ?>" <?= $selected ? 'selected' : '' ?>><?= e($user['name'] . ' • ' . $user['email']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label">Nazwa</label><input type="text" name="name" class="form-control" value="<?= e($exercise['name']) ?>"></div>
                    <div class="col-md-6"><label class="form-label">Partia miesniowa</label><input type="text" name="muscle_group" class="form-control" value="<?= e($exercise['muscle_group']) ?>"></div>
                    <div class="col-md-6"><label class="form-label">Sprzet</label><input type="text" name="equipment" class="form-control" value="<?= e($exercise['equipment'] ?? '') ?>"></div>
                    <div class="col-12"><label class="form-label">Opis</label><textarea name="description" rows="4" class="form-control"><?= e($exercise['description'] ?? '') ?></textarea></div>
                </div>
                <button class="btn btn-primary mt-4">Zapisz zmiany</button>
            </form>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
