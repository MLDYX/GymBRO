<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/bootstrap.php';
require_admin();

$eventId = (int) ($_GET['id'] ?? 0);
$eventRepository = new EventRepository(pdo());
$gymRepository = new GymRepository(pdo());
$userRepository = new UserRepository(pdo());
$event = $eventRepository->find($eventId);

if (!$event) {
    redirect_with_flash('/admin/events/index.php', 'danger', 'Nie znaleziono wydarzenia.');
}

$gyms = $gymRepository->all();
$users = $userRepository->all();

if (is_post()) {
    verify_csrf();
    if ($eventRepository->updateAdmin($eventId, $_POST)) {
        redirect_with_flash('/admin/events/index.php', 'success', 'Wydarzenie zostalo zaktualizowane.');
    }
}

$pageTitle = 'Admin • Edytuj wydarzenie';
require_once base_path('app/includes/header.php');
?>
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card p-4">
            <h1 class="h3 mb-3">Edytuj wydarzenie</h1>
            <form method="post">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Tworca</label>
                        <select name="creator_id" class="form-select">
                            <?php foreach ($users as $user): ?>
                                <option value="<?= e((string) $user['id']) ?>" <?= (int) $event['creator_id'] === (int) $user['id'] ? 'selected' : '' ?>><?= e($user['name'] . ' • ' . $user['email']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Silownia</label>
                        <select name="gym_id" class="form-select">
                            <option value="">Brak</option>
                            <?php foreach ($gyms as $gym): ?>
                                <option value="<?= e((string) $gym['id']) ?>" <?= (string) ($event['gym_id'] ?? '') === (string) $gym['id'] ? 'selected' : '' ?>><?= e($gym['name'] . ' • ' . $gym['city']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-8"><label class="form-label">Tytul</label><input type="text" name="title" class="form-control" value="<?= e($event['title']) ?>"></div>
                    <div class="col-md-4"><label class="form-label">Liczba miejsc</label><input type="number" min="2" name="max_participants" class="form-control" value="<?= e((string) $event['max_participants']) ?>"></div>
                    <div class="col-md-4"><label class="form-label">Data</label><input type="date" name="event_date" class="form-control" value="<?= e($event['event_date']) ?>"></div>
                    <div class="col-md-4"><label class="form-label">Godzina</label><input type="time" name="start_time" class="form-control" value="<?= e(substr((string) $event['start_time'], 0, 5)) ?>"></div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <?php foreach (['planned', 'completed', 'cancelled'] as $status): ?>
                                <option value="<?= e($status) ?>" <?= $event['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12"><label class="form-label">Opis</label><textarea name="description" rows="4" class="form-control"><?= e($event['description'] ?? '') ?></textarea></div>
                </div>
                <button class="btn btn-primary mt-4">Zapisz zmiany</button>
            </form>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
