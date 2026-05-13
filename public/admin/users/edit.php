<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/bootstrap.php';
require_admin();

$userId = (int) ($_GET['id'] ?? 0);
$userRepository = new UserRepository(pdo());
$profileRepository = new ProfileRepository(pdo());
$user = $userRepository->findById($userId);

if (!$user) {
    redirect_with_flash('/admin/users/index.php', 'danger', 'Nie znaleziono uzytkownika.');
}

$errors = [];
$experienceOptions = ['ponizej roku', '1-2 lata', '3+ lata'];
$levelOptions = ['poczatkujacy', 'sredniozaawansowany', 'zaawansowany'];

if (is_post()) {
    verify_csrf();

    $errors = validate_required($_POST, [
        'name' => 'Imie i nazwisko',
        'email' => 'E-mail',
    ]);

    if (!filter_var((string) ($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Podaj poprawny adres e-mail.';
    }

    if (!$errors) {
        $existing = $userRepository->findByEmail((string) $_POST['email']);
        if ($existing && (int) $existing['id'] !== $userId) {
            $errors['email'] = 'Uzytkownik o podanym adresie e-mail juz istnieje.';
        }
    }

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
        $userRepository->updateAccount($userId, $_POST);
        $profileRepository->update($userId, [
            'age' => $_POST['age'] ?? '',
            'height_cm' => $_POST['height_cm'] ?? '',
            'weight_kg' => $_POST['weight_kg'] ?? '',
            'training_level' => $_POST['training_level'] ?? '',
            'training_experience' => $_POST['training_experience'] ?? '',
            'goal' => $_POST['goal'] ?? '',
            'bio' => $_POST['bio'] ?? '',
            'avatar_path' => $_POST['avatar_path'] ?? '',
            'onboarding_completed' => admin_bool_value($_POST['onboarding_completed'] ?? false),
        ]);

        if ($userId === current_user_id()) {
            $_SESSION['user_name'] = trim((string) ($_POST['name'] ?? ''));
            $_SESSION['user_email'] = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
        }

        redirect_with_flash('/admin/users/index.php', 'success', 'Dane uzytkownika zostaly zaktualizowane.');
    }
}

$profile = $profileRepository->findByUserId($userId) ?? [];
$pageTitle = 'Admin • Edytuj uzytkownika';
require_once base_path('app/includes/header.php');
?>
<div class="row justify-content-center">
    <div class="col-xl-9">
        <div class="card p-4">
            <div class="panel-title">
                <div>
                    <h1 class="h3 mb-1">Edytuj uzytkownika</h1>
                    <div class="meta-line">ID #<?= e((string) $userId) ?></div>
                </div>
                <a href="<?= e(url('/admin/users/index.php')) ?>" class="btn btn-outline-primary">Powrot</a>
            </div>
            <form method="post">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Imie i nazwisko</label>
                        <input type="text" name="name" class="form-control" value="<?= e((string) ($_POST['name'] ?? $user['name'])) ?>">
                        <?php if (isset($errors['name'])): ?><small class="text-danger"><?= e($errors['name']) ?></small><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" class="form-control" value="<?= e((string) ($_POST['email'] ?? $user['email'])) ?>">
                        <?php if (isset($errors['email'])): ?><small class="text-danger"><?= e($errors['email']) ?></small><?php endif; ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Wiek</label>
                        <input type="number" name="age" class="form-control" value="<?= e((string) ($_POST['age'] ?? ($profile['age'] ?? ''))) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Wzrost (cm)</label>
                        <input type="number" name="height_cm" class="form-control" value="<?= e((string) ($_POST['height_cm'] ?? ($profile['height_cm'] ?? ''))) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Waga (kg)</label>
                        <input type="number" step="0.01" name="weight_kg" class="form-control" value="<?= e((string) ($_POST['weight_kg'] ?? ($profile['weight_kg'] ?? ''))) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Avatar path</label>
                        <input type="text" name="avatar_path" class="form-control" value="<?= e((string) ($_POST['avatar_path'] ?? ($profile['avatar_path'] ?? ''))) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Poziom</label>
                        <select name="training_level" class="form-select">
                            <option value="">Wybierz poziom</option>
                            <?php foreach ($levelOptions as $option): ?>
                                <?php $selected = (string) ($_POST['training_level'] ?? ($profile['training_level'] ?? '')) === $option; ?>
                                <option value="<?= e($option) ?>" <?= $selected ? 'selected' : '' ?>><?= e(ucfirst($option)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Staz treningowy</label>
                        <select name="training_experience" class="form-select">
                            <option value="">Wybierz staz</option>
                            <?php foreach ($experienceOptions as $option): ?>
                                <?php $selected = (string) ($_POST['training_experience'] ?? ($profile['training_experience'] ?? '')) === $option; ?>
                                <option value="<?= e($option) ?>" <?= $selected ? 'selected' : '' ?>><?= e(ucfirst($option)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Cel</label>
                        <input type="text" name="goal" class="form-control" value="<?= e((string) ($_POST['goal'] ?? ($profile['goal'] ?? ''))) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Onboarding zakonczony</label>
                        <select name="onboarding_completed" class="form-select">
                            <?php $onboardingCompleted = admin_bool_value($_POST['onboarding_completed'] ?? ($profile['onboarding_completed'] ?? false)); ?>
                            <option value="0" <?= !$onboardingCompleted ? 'selected' : '' ?>>Nie</option>
                            <option value="1" <?= $onboardingCompleted ? 'selected' : '' ?>>Tak</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Bio</label>
                        <textarea name="bio" rows="5" class="form-control"><?= e((string) ($_POST['bio'] ?? ($profile['bio'] ?? ''))) ?></textarea>
                    </div>
                </div>
                <button class="btn btn-primary mt-4">Zapisz zmiany</button>
            </form>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
