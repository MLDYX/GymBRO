<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/bootstrap.php';
require_admin();

$eventRepository = new EventRepository(pdo());
$gymRepository = new GymRepository(pdo());
$userRepository = new UserRepository(pdo());
$gyms = $gymRepository->all();
$users = $userRepository->all();
$errors = [];

if (is_post()) {
    verify_csrf();
    $errors = validate_required($_POST, [
        'creator_id' => 'Tworca',
        'title' => 'Tytul',
        'event_date' => 'Data',
        'start_time' => 'Godzina',
    ]);

    if (!$errors) {
        $eventId = $eventRepository->create((int) $_POST['creator_id'], $_POST);
        if (($_POST['status'] ?? 'planned') !== 'planned') {
            $eventRepository->updateAdmin($eventId, array_merge($_POST, ['creator_id' => $_POST['creator_id']]));
        }
        redirect_with_flash('/admin/events/edit.php?id=' . $eventId, 'success', 'Wydarzenie zostalo utworzone.');
    }
}

$pageTitle = 'Admin • Dodaj wydarzenie';
require_once base_path('app/includes/header.php');
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card p-4">
            <h1 class="h3 mb-3">Dodaj wydarzenie</h1>
            <form method="post">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Tworca</label>
                        <select name="creator_id" class="form-select">
                            <option value="">Wybierz uzytkownika</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= e((string) $user['id']) ?>" <?= old('creator_id') === (string) $user['id'] ? 'selected' : '' ?>><?= e($user['name'] . ' • ' . $user['email']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Silownia</label>
                        <select name="gym_id" class="form-select">
                            <option value="">Brak</option>
                            <?php foreach ($gyms as $gym): ?>
                                <option value="<?= e((string) $gym['id']) ?>" <?= old('gym_id') === (string) $gym['id'] ? 'selected' : '' ?>><?= e($gym['name'] . ' • ' . $gym['city']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-8"><label class="form-label">Tytul</label><input type="text" name="title" class="form-control" value="<?= old('title') ?>"></div>
                    <div class="col-md-4"><label class="form-label">Liczba miejsc</label><input type="number" min="2" name="max_participants" class="form-control" value="<?= old('max_participants', '2') ?>"></div>
                    <div class="col-md-4"><label class="form-label">Data</label><input type="date" name="event_date" class="form-control" value="<?= old('event_date') ?>"></div>
                    <div class="col-md-4"><label class="form-label">Godzina</label><input type="time" name="start_time" class="form-control" value="<?= old('start_time') ?>"></div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="planned">planned</option>
                            <option value="completed">completed</option>
                            <option value="cancelled">cancelled</option>
                        </select>
                    </div>
                    <div class="col-12"><label class="form-label">Opis</label><textarea name="description" rows="4" class="form-control"><?= old('description') ?></textarea></div>
                </div>
                <button class="btn btn-primary mt-4">Dodaj wydarzenie</button>
            </form>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
