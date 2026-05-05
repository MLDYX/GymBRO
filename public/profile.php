<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';
require_login();

$profileRepository = new ProfileRepository(pdo());
$profile = $profileRepository->findByUserId(current_user_id()) ?? [];
$errors = [];
$experienceOptions = ['poniżej roku', '1-2 lata', '3+ lata'];
$levelOptions = ['początkujący', 'średniozaawansowany', 'zaawansowany'];

if (is_post()) {
    if (request_exceeds_post_limit()) {
        redirect_with_flash(
            '/profile.php',
            'danger',
            'Przeslany formularz jest za duzy. Zmniejsz rozmiar zdjecia profilowego i sproboj ponownie.'
        );
    }

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

    if ($message = validate_uploaded_image($_FILES['avatar'] ?? [])) {
        $errors['avatar'] = $message;
    }

    if (!$errors) {
        $avatarPath = $profile['avatar_path'] ?? null;
        if (isset($_FILES['avatar']) && ($_FILES['avatar']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $extension = uploaded_image_extension($_FILES['avatar']);
            $fileName = 'avatar_' . current_user_id() . '_' . time() . '.' . $extension;
            $target = public_path('uploads/avatars/' . $fileName);
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target)) {
                $avatarPath = '/uploads/avatars/' . $fileName;
            }
        }

        $profileRepository->update(current_user_id(), [
            'age' => $_POST['age'] ?? '',
            'height_cm' => $_POST['height_cm'] ?? '',
            'weight_kg' => $_POST['weight_kg'] ?? '',
            'training_level' => $_POST['training_level'] ?? '',
            'training_experience' => $_POST['training_experience'] ?? '',
            'goal' => $_POST['goal'] ?? '',
            'bio' => $_POST['bio'] ?? '',
            'avatar_path' => $avatarPath ?? '',
            'onboarding_completed' => true,
        ]);
        redirect_with_flash('/profile.php', 'success', 'Profil został zaktualizowany.');
    }
}

$profile = $profileRepository->findByUserId(current_user_id()) ?? [];
$pageTitle = 'Profil';
require_once base_path('app/includes/header.php');
?>
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">Twój profil</h1>
                    <p class="muted mb-0">Edytuj dane z onboardingu, zdjęcie i podstawy treningowe.</p>
                </div>
            </div>
            <form method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Zdjęcie profilowe</label>
                        <?php if (!empty($profile['avatar_path'])): ?>
                            <div class="mb-2"><img src="<?= e($profile['avatar_path']) ?>" alt="Avatar" class="rounded-4 border" style="width: 100px; height: 100px; object-fit: cover;"></div>
                        <?php endif; ?>
                        <input type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp" class="form-control">
                        <?php if (isset($errors['avatar'])): ?><small class="text-danger"><?= e($errors['avatar']) ?></small><?php endif; ?>
                    </div>
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
                    <div class="col-md-4">
                        <label class="form-label">Poziom zaawansowania</label>
                        <select name="training_level" class="form-select">
                            <option value="">Wybierz poziom</option>
                            <?php foreach ($levelOptions as $option): ?>
                                <option value="<?= e($option) ?>" <?= (($profile['training_level'] ?? '') === $option) ? 'selected' : '' ?>><?= e(ucfirst($option)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Staż treningowy</label>
                        <select name="training_experience" class="form-select">
                            <option value="">Wybierz staż</option>
                            <?php foreach ($experienceOptions as $option): ?>
                                <option value="<?= e($option) ?>" <?= (($profile['training_experience'] ?? '') === $option) ? 'selected' : '' ?>><?= e(ucfirst($option)) ?></option>
                            <?php endforeach; ?>
                        </select>
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
