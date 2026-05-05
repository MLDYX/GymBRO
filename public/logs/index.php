<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();

$repository = new WorkoutLogRepository(mongo_db());
$logs = $repository->findByUser(current_user_id());

$pageTitle = 'Treningi';
require_once base_path('app/includes/header.php');
?>
<div class="context-tabs mb-4">
    <a class="<?= nav_active(['/logs']) ?>" href="/logs/index.php">Ostatnie treningi</a>
    <a class="<?= nav_active(['/progress']) ?>" href="/progress/index.php">Progres</a>
    <a href="/plans/index.php">Twoje plany</a>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="section-kicker mb-2">Treningi</p>
        <h1 class="h3 mb-1">Twoje ostatnie treningi</h1>
        <p class="muted mb-0">Najpierw szybki wgląd, potem szczegóły serii i ciężarów.</p>
    </div>
    <a href="/logs/create.php" class="btn btn-primary">Dodaj trening</a>
</div>

<div class="card p-4 mb-4">
    <div class="d-flex flex-wrap gap-3">
        <a href="/progress/index.php" class="btn btn-outline-primary">Zobacz progres</a>
        <a href="/events/index.php" class="btn btn-outline-primary">Zobacz spotkania</a>
    </div>
</div>

<div class="stack-list">
    <?php if (!$logs): ?>
        <div class="empty-state">Nie masz jeszcze żadnego wpisu. Dodaj pierwszy trening i zacznij budować historię.</div>
    <?php else: ?>
        <?php foreach ($logs as $log): ?>
            <div class="log-card">
                <div class="d-flex flex-wrap justify-content-between gap-3">
                    <div>
                        <div class="fw-semibold fs-5"><?= e((string) $log->training_date) ?></div>
                        <div class="meta-line"><?= e((string) $log->duration_minutes) ?> min • nastrój: <?= e((string) $log->mood) ?></div>
                        <p class="mb-0 mt-2"><?= e((string) $log->notes) ?></p>
                    </div>
                    <div class="d-flex align-items-start">
                        <a class="btn btn-outline-primary btn-sm" href="/logs/show.php?id=<?= e(mongo_id_string($log)) ?>">Szczegóły</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
