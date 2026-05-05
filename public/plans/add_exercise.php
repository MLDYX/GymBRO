<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();

$dayId = (int) ($_GET['day_id'] ?? 0);
$planId = (int) ($_GET['plan_id'] ?? 0);
$planRepository = new TrainingPlanRepository(pdo());
$exerciseRepository = new ExerciseRepository(pdo());
$exercises = $exerciseRepository->all();

if (is_post()) {
    verify_csrf();
    if ($planRepository->addExerciseToDay($dayId, current_user_id(), $_POST)) {
        redirect_with_flash('/plans/show.php?id=' . $planId, 'success', 'Ćwiczenie zostało dodane do dnia.');
    }
    redirect_with_flash('/plans/show.php?id=' . $planId, 'warning', 'Nie udało się dodać ćwiczenia.');
}

$pageTitle = 'Dodaj cwiczenie do dnia';
require_once base_path('app/includes/header.php');
?>
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card p-4">
            <h1 class="h3 mb-3">Dodaj ćwiczenie do dnia</h1>
            <form method="post">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Ćwiczenie</label>
                        <select name="exercise_id" class="form-select">
                            <?php foreach ($exercises as $exercise): ?>
                                <option value="<?= e((string) $exercise['id']) ?>"><?= e($exercise['name'] . ' (' . $exercise['muscle_group'] . ')') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4"><label class="form-label">Serie</label><input type="number" min="1" name="sets" class="form-control" value="4"></div>
                    <div class="col-md-4"><label class="form-label">Powtórzenia</label><input type="text" name="reps" class="form-control" value="8-12"></div>
                    <div class="col-md-4"><label class="form-label">Przerwa (s)</label><input type="number" min="0" name="rest_seconds" class="form-control" value="90"></div>
                    <div class="col-md-6"><label class="form-label">Kolejność</label><input type="number" min="1" name="exercise_order" class="form-control" value="1"></div>
                    <div class="col-12"><label class="form-label">Notatki</label><textarea name="notes" rows="4" class="form-control"></textarea></div>
                </div>
                <button class="btn btn-primary mt-4">Dodaj ćwiczenie</button>
            </form>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
