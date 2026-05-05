<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();

$planId = (int) ($_GET['id'] ?? 0);
$planRepository = new TrainingPlanRepository(pdo());
$commentRepository = new CommentRepository(mongo_db());
$plan = $planRepository->getPlanWithDaysAndExercises($planId);

if (!$plan || !$planRepository->canAccess($planId, current_user_id())) {
    redirect_with_flash('/plans/index.php', 'danger', 'Nie masz dostępu do tego planu.');
}

$comments = $commentRepository->getPlanComments($planId);
$pageTitle = 'Szczegóły planu';
require_once base_path('app/includes/header.php');
?>
<div class="row g-4">
    <div class="col-xl-8">
        <div class="card p-4 mb-4">
            <div class="d-flex flex-wrap justify-content-between gap-3 mb-3">
                <div>
                    <h1 class="h3 mb-1"><?= e($plan['name']) ?></h1>
                    <p class="muted mb-0">Autor: <?= e($plan['author_name']) ?> • <?= e($plan['visibility'] === 'public' ? 'publiczny' : 'prywatny') ?></p>
                </div>
                <?php if ((int) $plan['user_id'] === current_user_id()): ?>
                    <div class="d-flex gap-2">
                        <a class="btn btn-outline-primary" href="/plans/add_day.php?id=<?= e((string) $plan['id']) ?>">Dodaj dzień</a>
                        <a class="btn btn-outline-primary" href="/exercises/index.php">Ćwiczenia</a>
                        <form method="post" action="/plans/delete.php?id=<?= e((string) $plan['id']) ?>">
                            <?= csrf_field() ?>
                            <button class="btn btn-outline-danger">Usuń plan</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
            <p><?= e($plan['description'] ?? 'Brak opisu planu.') ?></p>
            <div class="row g-3">
                <div class="col-md-6"><div class="stat-card p-3"><strong>Poziom:</strong> <?= e($plan['level'] ?? 'Brak') ?></div></div>
                <div class="col-md-6"><div class="stat-card p-3"><strong>Cel:</strong> <?= e($plan['goal'] ?? 'Brak') ?></div></div>
            </div>
        </div>

        <?php foreach ($plan['days'] as $day): ?>
            <div class="card p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="h4 mb-0"><?= e($day['name']) ?></h2>
                        <div class="muted small">Dzień <?= e((string) $day['day_order']) ?></div>
                    </div>
                    <?php if ((int) $plan['user_id'] === current_user_id()): ?>
                        <a class="btn btn-outline-primary btn-sm" href="/plans/add_exercise.php?day_id=<?= e((string) $day['id']) ?>&plan_id=<?= e((string) $plan['id']) ?>">Dodaj ćwiczenie</a>
                    <?php endif; ?>
                </div>
                <?php if (!$day['exercises']): ?>
                    <div class="empty-state">Brak ćwiczeń w tym dniu.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead><tr><th>Ćwiczenie</th><th>Serie</th><th>Powtórzenia</th><th>Przerwa</th><th>Notatki</th><?php if ((int) $plan['user_id'] === current_user_id()): ?><th></th><?php endif; ?></tr></thead>
                            <tbody>
                            <?php foreach ($day['exercises'] as $exercise): ?>
                                <tr>
                                    <td><?= e($exercise['exercise_name']) ?></td>
                                    <td><?= e((string) $exercise['sets']) ?></td>
                                    <td><?= e($exercise['reps']) ?></td>
                                    <td><?= e((string) ($exercise['rest_seconds'] ?? 0)) ?> s</td>
                                    <td><?= e($exercise['notes'] ?? '-') ?></td>
                                    <?php if ((int) $plan['user_id'] === current_user_id()): ?>
                                        <td>
                                            <form method="post" action="/plans/delete_exercise.php?id=<?= e((string) $exercise['id']) ?>&plan_id=<?= e((string) $plan['id']) ?>">
                                                <?= csrf_field() ?>
                                                <button class="btn btn-outline-danger btn-sm">Usuń</button>
                                            </form>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="col-xl-4">
        <div class="card p-4 mb-4">
            <h2 class="h4 mb-3">Dodaj komentarz</h2>
            <form method="post" action="/plans/comment.php?id=<?= e((string) $plan['id']) ?>">
                <?= csrf_field() ?>
                <div class="mb-3"><label class="form-label">Ocena</label><select name="rating" class="form-select"><?php for ($i = 1; $i <= 5; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?></select></div>
                <div class="mb-3"><label class="form-label">Komentarz</label><textarea name="content" class="form-control" rows="4"></textarea></div>
                <button class="btn btn-primary w-100">Dodaj komentarz</button>
            </form>
        </div>

        <div class="card p-4">
            <h2 class="h4 mb-3">Komentarze</h2>
            <?php if (!$comments): ?>
                <div class="empty-state">Brak komentarzy.</div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($comments as $comment): ?>
                        <div class="list-group-item px-0 py-3">
                            <div class="d-flex justify-content-between mb-1">
                                <strong><?= e((string) $comment->user_name) ?></strong>
                                <span class="badge text-bg-light"><?= e((string) $comment->rating) ?>/5</span>
                            </div>
                            <div class="small mb-1"><?= e((string) $comment->content) ?></div>
                            <div class="muted small"><?= e((string) $comment->created_at) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
