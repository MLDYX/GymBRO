<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/bootstrap.php';
require_admin();

$gymId = (int) ($_GET['id'] ?? 0);
$repository = new GymRepository(pdo());
$userRepository = new UserRepository(pdo());
$gym = $repository->find($gymId);

if (!$gym) {
    redirect_with_flash('/admin/gyms/index.php', 'danger', 'Nie znaleziono silowni.');
}

$users = $userRepository->all();

if (is_post()) {
    verify_csrf();
    if ($repository->updateAdmin($gymId, $_POST)) {
        redirect_with_flash('/admin/gyms/index.php', 'success', 'Silownia zostala zaktualizowana.');
    }
}

$pageTitle = 'Admin • Edytuj silownie';
require_once base_path('app/includes/header.php');
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card p-4">
            <h1 class="h3 mb-3">Edytuj silownie</h1>
            <form method="post">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Wlasciciel</label>
                        <select name="user_id" class="form-select">
                            <option value="">Bez wlasciciela</option>
                            <?php foreach ($users as $user): ?>
                                <?php $selected = (string) ($gym['user_id'] ?? '') === (string) $user['id']; ?>
                                <option value="<?= e((string) $user['id']) ?>" <?= $selected ? 'selected' : '' ?>><?= e($user['name'] . ' • ' . $user['email']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label">Nazwa</label><input type="text" name="name" class="form-control" value="<?= e($gym['name']) ?>"></div>
                    <div class="col-md-6"><label class="form-label">Miasto</label><input type="text" name="city" class="form-control" value="<?= e($gym['city']) ?>"></div>
                    <div class="col-md-6"><label class="form-label">Adres</label><input type="text" name="address" class="form-control" value="<?= e($gym['address'] ?? '') ?>"></div>
                    <div class="col-12"><label class="form-label">Opis</label><textarea name="description" rows="4" class="form-control"><?= e($gym['description'] ?? '') ?></textarea></div>
                </div>
                <button class="btn btn-primary mt-4">Zapisz zmiany</button>
            </form>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
