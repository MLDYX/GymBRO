<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();

$repository = new EventRepository(pdo());
$event = $repository->find((int) ($_GET['id'] ?? 0));

if (!$event) {
    redirect_with_flash('/events/index.php', 'danger', 'Nie znaleziono wydarzenia.');
}

$isParticipant = $repository->isParticipant((int) $event['id'], current_user_id());
$pageTitle = 'Szczegoly wydarzenia';
require_once base_path('app/includes/header.php');
?>
<div class="row justify-content-center">
    <div class="col-xl-9">
        <div class="card p-4">
            <div class="d-flex flex-wrap justify-content-between gap-3 mb-4">
                <div>
                    <h1 class="h3 mb-1"><?= e($event['title']) ?></h1>
                    <p class="muted mb-0"><?= e($event['event_date']) ?> | <?= e($event['start_time']) ?></p>
                </div>
                <div class="d-flex gap-2">
                    <?php if ((int) $event['creator_id'] !== current_user_id() && !$isParticipant && (int) $event['free_slots'] > 0): ?>
                        <form method="post" action="/events/join.php?id=<?= e((string) $event['id']) ?>">
                            <?= csrf_field() ?>
                            <button class="btn btn-primary">Dołącz</button>
                        </form>
                    <?php endif; ?>
                    <?php if ($isParticipant): ?>
                        <form method="post" action="/events/leave.php?id=<?= e((string) $event['id']) ?>">
                            <?= csrf_field() ?>
                            <button class="btn btn-outline-danger">Opuść</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-7">
                    <p><?= e($event['description'] ?? 'Brak opisu wydarzenia.') ?></p>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-0"><strong>Siłownia:</strong> <?= e($event['gym_name'] ?? 'Nie wybrano') ?></li>
                        <li class="list-group-item px-0"><strong>Adres:</strong> <?= e($event['gym_address'] ?? '-') ?></li>
                        <li class="list-group-item px-0"><strong>Twórca:</strong> <?= e($event['creator_name']) ?></li>
                        <li class="list-group-item px-0"><strong>Wolne miejsca:</strong> <?= e((string) $event['free_slots']) ?></li>
                    </ul>
                </div>
                <div class="col-lg-5">
                    <h2 class="h5 mb-3">Uczestnicy</h2>
                    <div class="list-group list-group-flush">
                        <?php foreach ($event['participants'] as $participant): ?>
                            <div class="list-group-item px-0 py-3"><?= e($participant['name']) ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
