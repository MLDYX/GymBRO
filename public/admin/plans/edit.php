<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/bootstrap.php';
require_admin();

$planId = (int) ($_GET['id'] ?? 0);
$repository = new TrainingPlanRepository(pdo());
$userRepository = new UserRepository(pdo());
$plan = $repository->getPlanWithDaysAndExercises($planId);

if (!$plan) {
    redirect_with_flash('/admin/plans/index.php', 'danger', 'Nie znaleziono planu.');
}

$users = $userRepository->all();

if (is_post()) {
    verify_csrf();
    if ($repository->updateAdmin($planId, $_POST)) {
        redirect_with_flash('/admin/plans/edit.php?id=' . $planId, 'success', 'Plan zostal zaktualizowany.');
    }
}

$pageTitle = 'Admin • Edytuj plan';
require_once base_path('app/includes/header.php');
?>
<div class="row g-4">
    <div class="col-xl-8">
        <div class="card p-4 mb-4">
            <div class="panel-title">
                <div>
                    <h1 class="h3 mb-1">Edytuj plan</h1>
                    <div class="meta-line">ID #<?= e((string) $plan['id']) ?></div>
                </div>
                <a href="<?= e(url('/admin/plans/index.php')) ?>" class="btn btn-outline-primary">Powrot</a>
            </div>
            <form method="post">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Autor</label>
                        <select name="user_id" class="form-select">
                            <?php foreach ($users as $user): ?>
                                <option value="<?= e((string) $user['id']) ?>" <?= (int) $plan['user_id'] === (int) $user['id'] ? 'selected' : '' ?>><?= e($user['name'] . ' • ' . $user['email']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Widocznosc</label>
                        <select name="visibility" class="form-select">
                            <option value="private" <?= $plan['visibility'] === 'private' ? 'selected' : '' ?>>private</option>
                            <option value="public" <?= $plan['visibility'] === 'public' ? 'selected' : '' ?>>public</option>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label">Nazwa</label><input type="text" name="name" class="form-control" value="<?= e($plan['name']) ?>"></div>
                    <div class="col-md-6"><label class="form-label">Poziom</label><input type="text" name="level" class="form-control" value="<?= e($plan['level'] ?? '') ?>"></div>
                    <div class="col-md-6"><label class="form-label">Cel</label><input type="text" name="goal" class="form-control" value="<?= e($plan['goal'] ?? '') ?>"></div>
                    <div class="col-12"><label class="form-label">Opis</label><textarea name="description" rows="4" class="form-control"><?= e($plan['description'] ?? '') ?></textarea></div>
                </div>
                <button class="btn btn-primary mt-4">Zapisz plan</button>
            </form>
        </div>

        <?php foreach ($plan['days'] as $day): ?>
            <div class="card p-4 mb-4">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <h2 class="h4 mb-1"><?= e($day['name']) ?></h2>
                        <div class="meta-line">Dzien <?= e((string) $day['day_order']) ?></div>
                    </div>
                    <div class="d-flex gap-2">
                        <a class="btn btn-outline-primary btn-sm" href="<?= e(url('/admin/plans/days/edit.php?id=' . (string) $day['id'])) ?>">Edytuj dzien</a>
                        <form method="post" action="<?= e(url('/admin/plans/days/delete.php?id=' . (string) $day['id'] . '&plan_id=' . (string) $plan['id'])) ?>">
                            <?= csrf_field() ?>
                            <button class="btn btn-outline-danger btn-sm">Usun dzien</button>
                        </form>
                        <a class="btn btn-primary btn-sm" href="<?= e(url('/admin/plans/exercises/create.php?day_id=' . (string) $day['id'] . '&plan_id=' . (string) $plan['id'])) ?>">Dodaj cwiczenie</a>
                    </div>
                </div>
                <?php if (!$day['exercises']): ?>
                    <div class="empty-state">Brak cwiczen w tym dniu.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead><tr><th>Cwiczenie</th><th>Serie</th><th>Powtorzenia</th><th>Przerwa</th><th>Kolejnosc</th><th>Akcje</th></tr></thead>
                            <tbody>
                            <?php foreach ($day['exercises'] as $exercise): ?>
                                <tr>
                                    <td><?= e($exercise['exercise_name']) ?></td>
                                    <td><?= e((string) $exercise['sets']) ?></td>
                                    <td><?= e($exercise['reps']) ?></td>
                                    <td><?= e((string) ($exercise['rest_seconds'] ?? 0)) ?> s</td>
                                    <td><?= e((string) $exercise['exercise_order']) ?></td>
                                    <td class="d-flex gap-2">
                                        <a class="btn btn-outline-primary btn-sm" href="<?= e(url('/admin/plans/exercises/edit.php?id=' . (string) $exercise['id'] . '&plan_id=' . (string) $plan['id'])) ?>">Edytuj</a>
                                        <form method="post" action="<?= e(url('/admin/plans/exercises/delete.php?id=' . (string) $exercise['id'] . '&plan_id=' . (string) $plan['id'])) ?>">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-outline-danger btn-sm">Usun</button>
                                        </form>
                                    </td>
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
        <div class="card p-4">
            <h2 class="h4 mb-3">Dodaj dzien</h2>
            <p class="muted mb-3">Nowy dzien treningowy do tego planu.</p>
            <a href="<?= e(url('/admin/plans/days/create.php?plan_id=' . (string) $plan['id'])) ?>" class="btn btn-primary w-100">Dodaj dzien</a>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
