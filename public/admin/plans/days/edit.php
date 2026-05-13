<?php

declare(strict_types=1);

require_once dirname(__DIR__, 4) . '/app/bootstrap.php';
require_admin();

$dayId = (int) ($_GET['id'] ?? 0);
$repository = new TrainingPlanRepository(pdo());
$day = $repository->findDay($dayId);

if (!$day) {
    redirect_with_flash('/admin/plans/index.php', 'danger', 'Nie znaleziono dnia planu.');
}

if (is_post()) {
    verify_csrf();
    if ($repository->updateDayAdmin($dayId, $_POST)) {
        redirect_with_flash('/admin/plans/edit.php?id=' . $day['plan_id'], 'success', 'Dzien zostal zaktualizowany.');
    }
}

$pageTitle = 'Admin • Edytuj dzien';
require_once base_path('app/includes/header.php');
?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card p-4">
            <h1 class="h3 mb-3">Edytuj dzien planu</h1>
            <form method="post">
                <?= csrf_field() ?>
                <div class="mb-3"><label class="form-label">Nazwa dnia</label><input type="text" name="name" class="form-control" value="<?= e($day['name']) ?>"></div>
                <div class="mb-3"><label class="form-label">Kolejnosc</label><input type="number" min="1" name="day_order" class="form-control" value="<?= e((string) $day['day_order']) ?>"></div>
                <button class="btn btn-primary">Zapisz dzien</button>
            </form>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
