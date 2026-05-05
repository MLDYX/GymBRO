<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();

$repository = new EventRepository(pdo());
$events = $repository->upcoming();

$pageTitle = 'Spotkania';
require_once base_path('app/includes/header.php');
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="section-kicker mb-2">Spotkania</p>
        <h1 class="h3 mb-1">Wspólne treningi</h1>
        <p class="muted mb-0">Tu najłatwiej ogarnąć, kto, gdzie i o której wpada na trening.</p>
    </div>
    <a href="/events/create.php" class="btn btn-primary">Umów trening</a>
</div>

<?php if (!$events): ?>
    <div class="empty-state">Nie ma jeszcze żadnych spotkań. Ustaw pierwsze wspólne wyjście i zbierz ekipę.</div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($events as $event): ?>
            <div class="col-lg-6">
                <div class="event-card h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h2 class="h4 mb-1"><?= e($event['title']) ?></h2>
                            <div class="muted"><?= e($event['event_date']) ?> | <?= e($event['start_time']) ?></div>
                        </div>
                        <span class="badge text-bg-light"><?= e((string) $event['participants_count']) ?>/<?= e((string) $event['max_participants']) ?></span>
                    </div>
                    <p class="small mb-3"><?= e($event['description'] ?? 'Brak opisu.') ?></p>
                    <p class="muted small mb-4"><?= e($event['gym_name'] ?? 'Bez siłowni') ?>, <?= e($event['gym_city'] ?? '-') ?> • ustawił <?= e($event['creator_name']) ?></p>
                    <a class="btn btn-outline-primary mt-auto" href="/events/show.php?id=<?= e((string) $event['id']) ?>">Zobacz szczegóły</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php require_once base_path('app/includes/footer.php'); ?>
