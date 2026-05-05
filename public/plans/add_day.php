<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();

$planId = (int) ($_GET['id'] ?? 0);
$repository = new TrainingPlanRepository(pdo());
$plan = $repository->find($planId);

if (!$plan || (int) $plan['user_id'] !== current_user_id()) {
    redirect_with_flash('/plans/index.php', 'danger', 'Nie możesz dodać dnia do tego planu.');
}

if (is_post()) {
    verify_csrf();
    if ($repository->addDay($planId, current_user_id(), $_POST)) {
        redirect_with_flash('/plans/show.php?id=' . $planId, 'success', 'Dodano nowy dzień treningowy.');
    }
}

$pageTitle = 'Dodaj dzien';
require_once base_path('app/includes/header.php');
?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card p-4">
            <h1 class="h3 mb-3">Dodaj dzień do planu</h1>
            <form method="post">
                <?= csrf_field() ?>
                <div class="mb-3"><label class="form-label">Nazwa dnia</label><input type="text" name="name" class="form-control" value="<?= old('name') ?>"></div>
                <div class="mb-3"><label class="form-label">Kolejność</label><input type="number" min="1" name="day_order" class="form-control" value="<?= old('day_order', '1') ?>"></div>
                <button class="btn btn-primary">Dodaj dzień</button>
            </form>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
