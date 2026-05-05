<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();

$repository = new ProgressRepository(mongo_db());
$selectedType = trim((string) ($_GET['type'] ?? 'body_weight'));
$measurements = $repository->findByUser(current_user_id(), $selectedType !== '' ? $selectedType : null);
$chartData = $repository->chartData(current_user_id(), $selectedType);
$types = [
    'body_weight',
    'bench_press',
    'squat',
    'deadlift',
    'arm_circumference',
    'chest_circumference',
    'waist_circumference',
];

$pageTitle = 'Progres';
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
        <h1 class="h3 mb-1">Progres</h1>
        <p class="muted mb-0">Waga, siła i obwody bez przeładowanego panelu.</p>
    </div>
    <a href="/progress/add.php" class="btn btn-primary">Dodaj pomiar</a>
</div>

<div class="card p-4 mb-4">
    <form method="get" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label">Typ pomiaru</label>
            <select name="type" class="form-select">
                <?php foreach ($types as $type): ?>
                    <option value="<?= e($type) ?>" <?= $selectedType === $type ? 'selected' : '' ?>><?= e(progress_type_label($type)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-primary w-100">Filtruj</button>
        </div>
    </form>
</div>

<div class="row g-4">
    <div class="col-xl-5">
        <div class="card p-4 h-100">
            <h2 class="h4 mb-3">Pomiary</h2>
            <?php if (!$measurements): ?>
                <div class="empty-state">Brak pomiarów dla wybranego typu.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th>Data</th><th>Wartość</th><th>Notatka</th></tr></thead>
                        <tbody>
                        <?php foreach ($measurements as $measurement): ?>
                            <tr>
                                <td><?= e((string) $measurement->date) ?></td>
                                <td><?= e((string) $measurement->value) ?> <?= e((string) $measurement->unit) ?></td>
                                <td><?= e((string) $measurement->note) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-xl-7">
        <div class="card p-4 h-100">
            <h2 class="h4 mb-3">Wykres</h2>
            <div class="chart-wrapper">
                <canvas data-chart data-label="<?= e(progress_type_label($selectedType)) ?>" data-labels='<?= e(json_encode($chartData['labels'], JSON_UNESCAPED_UNICODE)) ?>' data-values='<?= e(json_encode($chartData['values'])) ?>'></canvas>
            </div>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
