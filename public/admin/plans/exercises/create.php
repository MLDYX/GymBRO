<?php

declare(strict_types=1);

require_once dirname(__DIR__, 4) . '/app/bootstrap.php';
require_admin();

$dayId = (int) ($_GET['day_id'] ?? 0);
$planId = (int) ($_GET['plan_id'] ?? 0);
$planRepository = new TrainingPlanRepository(pdo());
$exerciseRepository = new ExerciseRepository(pdo());
$day = $planRepository->findDay($dayId);
$exercises = $exerciseRepository->all();

if (!$day) {
    redirect_with_flash('/admin/plans/index.php', 'danger', 'Nie znaleziono dnia planu.');
}

if (is_post()) {
    verify_csrf();
    if ($planRepository->addExerciseToDayAdmin($dayId, $_POST)) {
        redirect_with_flash('/admin/plans/edit.php?id=' . $planId, 'success', 'Cwiczenie zostalo dodane do dnia.');
    }
}

$pageTitle = 'Admin • Dodaj cwiczenie do planu';
require_once base_path('app/includes/header.php');
?>
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card p-4">
            <h1 class="h3 mb-3">Dodaj cwiczenie do dnia</h1>
            <form method="post">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Cwiczenie</label>
                        <select name="exercise_id" class="form-select">
                            <?php foreach ($exercises as $exercise): ?>
                                <option value="<?= e((string) $exercise['id']) ?>"><?= e($exercise['name'] . ' (' . $exercise['muscle_group'] . ')') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4"><label class="form-label">Serie</label><input type="number" min="1" name="sets" class="form-control" value="4"></div>
                    <div class="col-md-4"><label class="form-label">Powtorzenia</label><input type="text" name="reps" class="form-control" value="8-12"></div>
                    <div class="col-md-4"><label class="form-label">Przerwa (s)</label><input type="number" min="0" name="rest_seconds" class="form-control" value="90"></div>
                    <div class="col-md-6"><label class="form-label">Kolejnosc</label><input type="number" min="1" name="exercise_order" class="form-control" value="1"></div>
                    <div class="col-12"><label class="form-label">Notatki</label><textarea name="notes" rows="4" class="form-control"></textarea></div>
                </div>
                <button class="btn btn-primary mt-4">Dodaj cwiczenie</button>
            </form>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
