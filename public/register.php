<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';
require_guest();

$userRepository = new UserRepository(pdo());
$profileRepository = new ProfileRepository(pdo());
$activityRepository = new ActivityLogRepository(mongo_db());
$errors = [];

if (is_post()) {
    verify_csrf();

    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    $errors = validate_required($_POST, [
        'name' => 'Imie i nazwisko',
        'email' => 'E-mail',
        'password' => 'Haslo',
        'confirm_password' => 'Potwierdzenie hasla',
    ]);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Podaj poprawny adres e-mail.';
    }

    if ($message = validate_email_unique($userRepository->findByEmail($email))) {
        $errors['email'] = $message;
    }

    if ($message = validate_min_length($password, 6, 'Haslo')) {
        $errors['password'] = $message;
    }

    if ($password !== $confirmPassword) {
        $errors['confirm_password'] = 'Hasla musza byc identyczne.';
    }

    if (!$errors) {
        $userId = $userRepository->create($name, $email, password_hash($password, PASSWORD_DEFAULT));
        $profileRepository->createEmpty($userId);
        $activityRepository->create($userId, 'registered', ['email' => $email]);
        login_user($userId, $name);
        redirect_with_flash('/dashboard.php', 'success', 'Konto zostalo utworzone.');
    }
}

$pageTitle = 'Rejestracja';
require_once base_path('app/includes/header.php');
?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card p-4">
            <h1 class="h3 mb-3">Rejestracja</h1>
            <p class="muted">Załóż konto i zacznij prowadzić treningi w GymBRO.</p>
            <form method="post">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Imię i nazwisko</label>
                    <input type="text" name="name" class="form-control" value="<?= old('name') ?>">
                    <?php if (isset($errors['name'])): ?><small class="text-danger"><?= e($errors['name']) ?></small><?php endif; ?>
                </div>
                <div class="mb-3">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" class="form-control" value="<?= old('email') ?>">
                    <?php if (isset($errors['email'])): ?><small class="text-danger"><?= e($errors['email']) ?></small><?php endif; ?>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Hasło</label>
                        <input type="password" name="password" class="form-control">
                        <?php if (isset($errors['password'])): ?><small class="text-danger"><?= e($errors['password']) ?></small><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Potwierdź hasło</label>
                        <input type="password" name="confirm_password" class="form-control">
                        <?php if (isset($errors['confirm_password'])): ?><small class="text-danger"><?= e($errors['confirm_password']) ?></small><?php endif; ?>
                    </div>
                </div>
                <button class="btn btn-primary w-100 mt-4">Utwórz konto</button>
            </form>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
