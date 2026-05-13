<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/bootstrap.php';
require_admin();

$logId = (string) ($_GET['id'] ?? '');
$repository = new WorkoutLogRepository(mongo_db());
$userRepository = new UserRepository(pdo());
$planRepository = new TrainingPlanRepository(pdo());
$gymRepository = new GymRepository(pdo());
$exerciseRepository = new ExerciseRepository(pdo());
$log = $repository->findAdmin($logId);

if (!$log) {
    redirect_with_flash('/admin/logs/index.php', 'danger', 'Nie znaleziono logu treningowego.');
}

$users = $userRepository->all();
$plans = $planRepository->allAdmin();
$gyms = $gymRepository->all();
$exercises = $exerciseRepository->all();
$logArray = (array) $log;

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
            'name' => $matchingExercise['name'] ?? 'Cwiczenie',
            'sets' => $sets,
        ];
    }

    $payload = [
        'user_id' => (int) ($_POST['user_id'] ?? 0),
        'training_date' => $_POST['training_date'] ?? '',
        'plan_id' => ($_POST['plan_id'] ?? '') !== '' ? (int) $_POST['plan_id'] : null,
        'gym_id' => ($_POST['gym_id'] ?? '') !== '' ? (int) $_POST['gym_id'] : null,
        'duration_minutes' => (int) ($_POST['duration_minutes'] ?? 0),
        'mood' => trim((string) ($_POST['mood'] ?? '')),
        'notes' => trim((string) ($_POST['notes'] ?? '')),
        'exercises' => $exerciseEntries,
        'created_at' => str_replace('T', ' ', (string) ($_POST['created_at'] ?? now_string())),
    ];

    if ($repository->updateAdmin($logId, $payload)) {
        redirect_with_flash('/admin/logs/index.php', 'success', 'Log treningowy zostal zaktualizowany.');
    }
}

$pageTitle = 'Admin • Edytuj log';
require_once base_path('app/includes/header.php');
?>
<div class="row justify-content-center">
    <div class="col-xl-10">
        <div class="card p-4">
            <h1 class="h3 mb-3">Edytuj log treningowy</h1>
            <form method="post" id="admin-workout-log-form">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Uzytkownik</label>
                        <select name="user_id" class="form-select">
                            <?php foreach ($users as $user): ?>
                                <option value="<?= e((string) $user['id']) ?>" <?= (int) admin_value($log, 'user_id') === (int) $user['id'] ? 'selected' : '' ?>><?= e($user['name'] . ' • ' . $user['email']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3"><label class="form-label">Data</label><input type="date" name="training_date" class="form-control" value="<?= e((string) admin_value($log, 'training_date')) ?>"></div>
                    <div class="col-md-3">
                        <label class="form-label">Plan</label>
                        <select name="plan_id" class="form-select">
                            <option value="">Brak</option>
                            <?php foreach ($plans as $plan): ?>
                                <option value="<?= e((string) $plan['id']) ?>" <?= (string) admin_value($log, 'plan_id', '') === (string) $plan['id'] ? 'selected' : '' ?>><?= e($plan['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Silownia</label>
                        <select name="gym_id" class="form-select">
                            <option value="">Brak</option>
                            <?php foreach ($gyms as $gym): ?>
                                <option value="<?= e((string) $gym['id']) ?>" <?= (string) admin_value($log, 'gym_id', '') === (string) $gym['id'] ? 'selected' : '' ?>><?= e($gym['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3"><label class="form-label">Czas (min)</label><input type="number" name="duration_minutes" class="form-control" value="<?= e((string) admin_value($log, 'duration_minutes')) ?>"></div>
                    <div class="col-md-3"><label class="form-label">Nastroj</label><input type="text" name="mood" class="form-control" value="<?= e((string) admin_value($log, 'mood')) ?>"></div>
                    <div class="col-md-6"><label class="form-label">Created at</label><input type="datetime-local" name="created_at" class="form-control" value="<?= e(admin_format_datetime_local((string) admin_value($log, 'created_at'))) ?>"></div>
                    <div class="col-12"><label class="form-label">Notatki</label><textarea name="notes" class="form-control" rows="3"><?= e((string) admin_value($log, 'notes')) ?></textarea></div>
                </div>

                <hr class="my-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h4 mb-0">Cwiczenia i serie</h2>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="admin-add-exercise-btn">Dodaj cwiczenie</button>
                </div>
                <div id="admin-exercise-container"></div>
                <button class="btn btn-primary mt-4">Zapisz log</button>
            </form>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const exercises = <?= json_encode(array_map(static fn(array $exercise): array => ['id' => (int) $exercise['id'], 'name' => $exercise['name']], $exercises), JSON_UNESCAPED_UNICODE) ?>;
    const existingEntries = <?= json_encode(array_map(static function (array|object $entry): array {
        $source = is_object($entry) ? (array) $entry : $entry;
        $sets = [];
        foreach (($source['sets'] ?? []) as $set) {
            $setSource = is_object($set) ? (array) $set : $set;
            $sets[] = [
                'weight' => $setSource['weight'] ?? '',
                'reps' => $setSource['reps'] ?? '',
            ];
        }

        return [
            'exercise_id' => (int) ($source['exercise_id'] ?? 0),
            'sets' => $sets,
        ];
    }, (array) ($logArray['exercises'] ?? [])), JSON_UNESCAPED_UNICODE) ?>;

    const container = document.getElementById('admin-exercise-container');
    const addExerciseButton = document.getElementById('admin-add-exercise-btn');
    let exerciseIndex = 0;

    function renderOptions(selectedId) {
        return exercises.map((exercise) => `<option value="${exercise.id}" ${exercise.id === selectedId ? 'selected' : ''}>${exercise.name}</option>`).join('');
    }

    function createSetRow(index, setData = {}) {
        return `
            <div class="row g-3 set-row mb-2">
                <div class="col-md-5"><input type="number" step="0.5" name="set_weight[${index}][]" class="form-control" placeholder="Ciezar" value="${setData.weight ?? ''}"></div>
                <div class="col-md-5"><input type="number" name="set_reps[${index}][]" class="form-control" placeholder="Powtorzenia" value="${setData.reps ?? ''}"></div>
                <div class="col-md-2"><button type="button" class="btn btn-outline-danger w-100 remove-set-btn">Usun</button></div>
            </div>
        `;
    }

    function addExerciseBlock(entryData = null) {
        const wrapper = document.createElement('div');
        wrapper.className = 'border rounded-4 p-3 mb-3';
        const selectedExerciseId = entryData?.exercise_id ?? (exercises[0]?.id ?? 0);
        const setsHtml = (entryData?.sets?.length ? entryData.sets : [{}]).map((setData) => createSetRow(exerciseIndex, setData)).join('');

        wrapper.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <strong>Cwiczenie</strong>
                <button type="button" class="btn btn-outline-danger btn-sm remove-exercise-btn">Usun cwiczenie</button>
            </div>
            <div class="mb-3">
                <select name="exercise_id[${exerciseIndex}]" class="form-select">${renderOptions(selectedExerciseId)}</select>
            </div>
            <div class="sets-container">${setsHtml}</div>
            <button type="button" class="btn btn-outline-primary btn-sm add-set-btn">Dodaj serie</button>
        `;

        const currentIndex = exerciseIndex;
        wrapper.addEventListener('click', function (event) {
            if (event.target.classList.contains('add-set-btn')) {
                wrapper.querySelector('.sets-container').insertAdjacentHTML('beforeend', createSetRow(currentIndex));
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

    addExerciseButton.addEventListener('click', function () {
        addExerciseBlock();
    });

    if (existingEntries.length) {
        existingEntries.forEach((entry) => addExerciseBlock(entry));
    } else {
        addExerciseBlock();
    }
});
</script>
<?php require_once base_path('app/includes/footer.php'); ?>
