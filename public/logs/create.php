<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();

$planRepository = new TrainingPlanRepository(pdo());
$gymRepository = new GymRepository(pdo());
$exerciseRepository = new ExerciseRepository(pdo());
$logRepository = new WorkoutLogRepository(mongo_db());
$activityRepository = new ActivityLogRepository(mongo_db());

$plans = $planRepository->userPlans(current_user_id());
$gyms = $gymRepository->all();
$exercises = $exerciseRepository->all();

if (is_post()) {
    verify_csrf();

    $exerciseEntries = [];
    foreach ($_POST['exercise_id'] ?? [] as $index => $exerciseId) {
        $exerciseId = (int) $exerciseId;
        if ($exerciseId <= 0) {
            continue;
        }

        $matchingExercise = null;
        foreach ($exercises as $exercise) {
            if ((int) $exercise['id'] === $exerciseId) {
                $matchingExercise = $exercise;
                break;
            }
        }

        $sets = [];
        foreach (($_POST['set_weight'][$index] ?? []) as $setIndex => $weight) {
            $reps = $_POST['set_reps'][$index][$setIndex] ?? '';
            if ($weight === '' && $reps === '') {
                continue;
            }
            $sets[] = [
                'weight' => (float) $weight,
                'reps' => (int) $reps,
            ];
        }

        $exerciseEntries[] = [
            'exercise_id' => $exerciseId,
            'name' => $matchingExercise['name'] ?? 'Ćwiczenie',
            'sets' => $sets,
        ];
    }

    $payload = [
        'user_id' => current_user_id(),
        'training_date' => $_POST['training_date'],
        'plan_id' => $_POST['plan_id'] !== '' ? (int) $_POST['plan_id'] : null,
        'gym_id' => $_POST['gym_id'] !== '' ? (int) $_POST['gym_id'] : null,
        'duration_minutes' => (int) ($_POST['duration_minutes'] ?? 0),
        'mood' => trim((string) ($_POST['mood'] ?? '')),
        'notes' => trim((string) ($_POST['notes'] ?? '')),
        'exercises' => $exerciseEntries,
        'created_at' => now_string(),
    ];

    $logRepository->create($payload);
    $activityRepository->create(current_user_id(), 'created_workout_log', ['training_date' => $_POST['training_date']]);
    redirect_with_flash('/logs/index.php', 'success', 'Trening został zapisany.');
}

$pageTitle = 'Dodaj trening';
require_once base_path('app/includes/header.php');
?>
<div class="row justify-content-center">
    <div class="col-xl-10">
        <div class="card p-4">
            <div class="panel-title">
                <div>
                    <h1 class="h3 mb-1">Dodaj trening</h1>
                    <div class="meta-line">Szybki wpis po treningu. Serie i ciężary możesz dodać od razu pod spodem.</div>
                </div>
                <div class="d-flex gap-2">
                    <a href="/gyms/index.php" class="btn btn-outline-primary btn-sm">Siłownie</a>
                    <a href="/exercises/index.php" class="btn btn-outline-primary btn-sm">Ćwiczenia</a>
                </div>
            </div>
            <form method="post" id="workout-log-form">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-3"><label class="form-label">Data</label><input type="date" name="training_date" class="form-control" value="<?= old('training_date', date('Y-m-d')) ?>"></div>
                    <div class="col-md-3"><label class="form-label">Plan</label><select name="plan_id" class="form-select"><option value="">Brak</option><?php foreach ($plans as $plan): ?><option value="<?= e((string) $plan['id']) ?>"><?= e($plan['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-3"><label class="form-label">Siłownia</label><select name="gym_id" class="form-select"><option value="">Brak</option><?php foreach ($gyms as $gym): ?><option value="<?= e((string) $gym['id']) ?>"><?= e($gym['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-3"><label class="form-label">Czas (min)</label><input type="number" name="duration_minutes" class="form-control" value="<?= old('duration_minutes', '60') ?>"></div>
                    <div class="col-md-4"><label class="form-label">Nastrój</label><input type="text" name="mood" class="form-control" value="<?= old('mood', 'dobry') ?>"></div>
                    <div class="col-12"><label class="form-label">Notatki</label><textarea name="notes" class="form-control" rows="3"><?= old('notes') ?></textarea></div>
                </div>

                <hr class="my-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h4 mb-0">Ćwiczenia i serie</h2>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="add-exercise-btn">Dodaj ćwiczenie</button>
                </div>
                <div id="exercise-container"></div>
                <button class="btn btn-primary mt-4">Zapisz trening</button>
            </form>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const exercises = <?= json_encode(array_map(static fn(array $exercise): array => ['id' => (int) $exercise['id'], 'name' => $exercise['name']], $exercises), JSON_UNESCAPED_UNICODE) ?>;
    const container = document.getElementById('exercise-container');
    const addExerciseButton = document.getElementById('add-exercise-btn');
    let exerciseIndex = 0;

    function renderOptions() {
        return exercises.map((exercise) => `<option value="${exercise.id}">${exercise.name}</option>`).join('');
    }

    function createSetRow(index) {
        return `
            <div class="row g-3 set-row mb-2">
                <div class="col-md-5"><input type="number" step="0.5" name="set_weight[${index}][]" class="form-control" placeholder="Ciężar"></div>
                <div class="col-md-5"><input type="number" name="set_reps[${index}][]" class="form-control" placeholder="Powtórzenia"></div>
                <div class="col-md-2"><button type="button" class="btn btn-outline-danger w-100 remove-set-btn">Usuń</button></div>
            </div>
        `;
    }

    function addExerciseBlock() {
        const wrapper = document.createElement('div');
        wrapper.className = 'border rounded-4 p-3 mb-3';
        wrapper.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <strong>Ćwiczenie</strong>
                <button type="button" class="btn btn-outline-danger btn-sm remove-exercise-btn">Usuń ćwiczenie</button>
            </div>
            <div class="mb-3">
                <select name="exercise_id[${exerciseIndex}]" class="form-select">${renderOptions()}</select>
            </div>
            <div class="sets-container">${createSetRow(exerciseIndex)}</div>
            <button type="button" class="btn btn-outline-primary btn-sm add-set-btn">Dodaj serię</button>
        `;

        wrapper.addEventListener('click', function (event) {
            if (event.target.classList.contains('add-set-btn')) {
                wrapper.querySelector('.sets-container').insertAdjacentHTML('beforeend', createSetRow(exerciseIndex));
            }

            if (event.target.classList.contains('remove-set-btn')) {
                event.target.closest('.set-row').remove();
            }

            if (event.target.classList.contains('remove-exercise-btn')) {
                wrapper.remove();
            }
        });

        container.appendChild(wrapper);
        exerciseIndex += 1;
    }

    addExerciseButton.addEventListener('click', addExerciseBlock);
    addExerciseBlock();
});
</script>
<?php require_once base_path('app/includes/footer.php'); ?>
