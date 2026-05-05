<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();

$repository = new TrainingPlanRepository(pdo());
$myPlans = $repository->userPlans(current_user_id());
$publicPlans = $repository->publicPlans();

$pageTitle = 'Plany';
require_once base_path('app/includes/header.php');
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="section-kicker mb-2">Plany</p>
        <h1 class="h3 mb-1">Twoje rozpiski i inspiracje</h1>
        <p class="muted mb-0">Najpierw prosta lista, a dopiero potem szczegóły dni i ćwiczeń.</p>
    </div>
    <a href="/plans/create.php" class="btn btn-primary">Nowy plan</a>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card p-4 h-100">
            <div class="panel-title">
                <div>
                    <h2 class="h4 mb-1">Twoje plany</h2>
                    <div class="meta-line">Twoja baza do własnych treningów i wspólnego omawiania planu.</div>
                </div>
            </div>
            <?php if (!$myPlans): ?>
                <div class="empty-state">Nie masz jeszcze żadnego planu. Zacznij od prostego szkicu i rozbuduj go później.</div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($myPlans as $plan): ?>
                        <div class="list-group-item px-0 py-3 d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold"><?= e($plan['name']) ?></div>
                                <div class="muted small"><?= e($plan['visibility'] === 'public' ? 'publiczny' : 'prywatny') ?> • dni: <?= e((string) $plan['day_count']) ?></div>
                            </div>
                            <div class="d-flex gap-2">
                                <a class="btn btn-outline-primary btn-sm" href="/plans/show.php?id=<?= e((string) $plan['id']) ?>">Zobacz</a>
                                <a class="btn btn-outline-secondary btn-sm" href="/plans/edit.php?id=<?= e((string) $plan['id']) ?>">Edytuj</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card p-4 h-100">
            <div class="panel-title">
                <div>
                    <h2 class="h4 mb-1">Plany od innych</h2>
                    <div class="meta-line">Podglądnij, jak ćwiczą inni, bez przeładowania dodatkowymi opcjami.</div>
                </div>
            </div>
            <?php if (!$publicPlans): ?>
                <div class="empty-state">Brak publicznych planów.</div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($publicPlans as $plan): ?>
                        <div class="list-group-item px-0 py-3 d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold"><?= e($plan['name']) ?></div>
                                <div class="muted small"><?= e($plan['author_name']) ?> • <?= e($plan['goal'] ?? 'Brak celu') ?></div>
                            </div>
                            <a class="btn btn-outline-primary btn-sm" href="/plans/show.php?id=<?= e((string) $plan['id']) ?>">Zobacz</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
