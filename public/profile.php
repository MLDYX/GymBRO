<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';
require_login();

$profileRepository = new ProfileRepository(pdo());
$profile = $profileRepository->findByUserId(current_user_id()) ?? [];
$errors = [];

if (is_post()) {
    verify_csrf();
    foreach ([
        'age' => ['Wiek', 0, 120],
        'height_cm' => ['Wzrost', 0, 300],
        'weight_kg' => ['Waga', 0, 500],
    ] as $field => [$label, $min, $max]) {
        if ($message = validate_numeric_range($_POST[$field] ?? null, $label, (float) $min, (float) $max)) {
            $errors[$field] = $message;
        }
    }

    if (!$errors) {
        $profileRepository->update(current_user_id(), $_POST);
        redirect_with_flash('/profile.php', 'success', 'Profil zostal zaktualizowany.');
    }
}

$profile = $profileRepository->findByUserId(current_user_id()) ?? [];
$pageTitle = 'Profil';
require_once base_path('app/includes/header.php');
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">Twój profil</h1>
                    <p class="muted mb-0">Uzupełnij dane treningowe i cel.</p>
                </div>
            </div>
            <form method="post">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Wiek</label>
                        <input type="number" name="age" class="form-control" value="<?= e((string) ($profile['age'] ?? '')) ?>">
                        <?php if (isset($errors['age'])): ?><small class="text-danger"><?= e($errors['age']) ?></small><?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Wzrost (cm)</label>
                        <input type="number" name="height_cm" class="form-control" value="<?= e((string) ($profile['height_cm'] ?? '')) ?>">
                        <?php if (isset($errors['height_cm'])): ?><small class="text-danger"><?= e($errors['height_cm']) ?></small><?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Waga (kg)</label>
                        <input type="number" step="0.01" name="weight_kg" class="form-control" value="<?= e((string) ($profile['weight_kg'] ?? '')) ?>">
                        <?php if (isset($errors['weight_kg'])): ?><small class="text-danger"><?= e($errors['weight_kg']) ?></small><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Poziom zaawansowania</label>
                        <input type="text" name="training_level" class="form-control" value="<?= e((string) ($profile['training_level'] ?? '')) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cel</label>
                        <input type="text" name="goal" class="form-control" value="<?= e((string) ($profile['goal'] ?? '')) ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Bio</label>
                        <textarea name="bio" class="form-control" rows="5"><?= e((string) ($profile['bio'] ?? '')) ?></textarea>
                    </div>
                </div>
                <button class="btn btn-primary mt-4">Zapisz zmiany</button>
            </form>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
