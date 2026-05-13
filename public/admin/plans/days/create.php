<?php

declare(strict_types=1);

require_once dirname(__DIR__, 4) . '/app/bootstrap.php';
require_admin();

$planId = (int) ($_GET['plan_id'] ?? 0);
$repository = new TrainingPlanRepository(pdo());
$plan = $repository->find($planId);

if (!$plan) {
    redirect_with_flash('/admin/plans/index.php', 'danger', 'Nie znaleziono planu.');
}

if (is_post()) {
    verify_csrf();
    if ($repository->addDayAdmin($planId, $_POST)) {
        redirect_with_flash('/admin/plans/edit.php?id=' . $planId, 'success', 'Dzien zostal dodany.');
    }
}

$pageTitle = 'Admin • Dodaj dzien';
require_once base_path('app/includes/header.php');
?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card p-4">
            <h1 class="h3 mb-3">Dodaj dzien do planu</h1>
            <form method="post">
                <?= csrf_field() ?>
                <div class="mb-3"><label class="form-label">Nazwa dnia</label><input type="text" name="name" class="form-control" value="<?= old('name') ?>"></div>
                <div class="mb-3"><label class="form-label">Kolejnosc</label><input type="number" min="1" name="day_order" class="form-control" value="<?= old('day_order', '1') ?>"></div>
                <button class="btn btn-primary">Dodaj dzien</button>
            </form>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
