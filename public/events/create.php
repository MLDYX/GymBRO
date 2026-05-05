<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();

$eventRepository = new EventRepository(pdo());
$gymRepository = new GymRepository(pdo());
$activityRepository = new ActivityLogRepository(mongo_db());
$gyms = $gymRepository->all();
$errors = [];

if (is_post()) {
    verify_csrf();
    $errors = validate_required($_POST, [
        'title' => 'Tytuł',
        'event_date' => 'Data',
        'start_time' => 'Godzina',
    ]);
    if (!$errors) {
        $eventId = $eventRepository->create(current_user_id(), $_POST);
        $activityRepository->create(current_user_id(), 'created_event', ['event_id' => $eventId, 'title' => $_POST['title']]);
        redirect_with_flash('/events/show.php?id=' . $eventId, 'success', 'Spotkanie zostało utworzone.');
    }
}

$pageTitle = 'Umów trening';
require_once base_path('app/includes/header.php');
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card p-4">
            <div class="panel-title">
                <div>
                    <h1 class="h3 mb-1">Ustaw wspólny trening</h1>
                    <div class="meta-line">Wybierz miejsce, termin i liczbę osób. Resztę ogarniesz później.</div>
                </div>
                <a href="/gyms/index.php" class="btn btn-outline-primary btn-sm">Zarządzaj siłowniami</a>
            </div>
            <form method="post">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-8"><label class="form-label">Tytuł</label><input type="text" name="title" class="form-control" value="<?= old('title') ?>"></div>
                    <div class="col-md-4"><label class="form-label">Liczba miejsc</label><input type="number" min="2" name="max_participants" class="form-control" value="<?= old('max_participants', '2') ?>"></div>
                    <div class="col-md-6"><label class="form-label">Siłownia</label><select name="gym_id" class="form-select"><option value="">Wybierz</option><?php foreach ($gyms as $gym): ?><option value="<?= e((string) $gym['id']) ?>"><?= e($gym['name'] . ' - ' . $gym['city']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-3"><label class="form-label">Data</label><input type="date" name="event_date" class="form-control" value="<?= old('event_date') ?>"></div>
                    <div class="col-md-3"><label class="form-label">Godzina</label><input type="time" name="start_time" class="form-control" value="<?= old('start_time') ?>"></div>
                    <div class="col-12"><label class="form-label">Opis</label><textarea name="description" rows="4" class="form-control"><?= old('description') ?></textarea></div>
                </div>
                <button class="btn btn-primary mt-4">Zapisz spotkanie</button>
            </form>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
