<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';
require_guest();

$userRepository = new UserRepository(pdo());
$errors = [];

if (is_post()) {
    verify_csrf();

    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $errors = validate_required($_POST, ['email' => 'E-mail', 'password' => 'Haslo']);

    if (!$errors) {
        $user = $userRepository->findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $errors['credentials'] = 'Nieprawidlowy e-mail lub haslo.';
        } else {
            login_user((int) $user['id'], $user['name'], (string) $user['email']);
            $redirectPath = mb_strtolower((string) $user['email']) === admin_email()
                ? '/admin/dashboard.php'
                : '/dashboard.php';
            redirect_with_flash($redirectPath, 'success', 'Zalogowano pomyslnie.');
        }
    }
}

$pageTitle = 'Logowanie';
require_once base_path('app/includes/header.php');
?>
<div class="row justify-content-center">
    <div class="col-lg-5">
        <div class="card p-4">
            <h1 class="h3 mb-3">Logowanie</h1>
            <p class="muted">Zaloguj się do swojego konta GymBRO.</p>
            <?php if (isset($errors['credentials'])): ?>
                <div class="alert alert-danger"><?= e($errors['credentials']) ?></div>
            <?php endif; ?>
            <form method="post">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" class="form-control" value="<?= old('email') ?>">
                    <?php if (isset($errors['email'])): ?><small class="text-danger"><?= e($errors['email']) ?></small><?php endif; ?>
                </div>
                <div class="mb-3">
                    <label class="form-label">Hasło</label>
                    <input type="password" name="password" class="form-control">
                    <?php if (isset($errors['password'])): ?><small class="text-danger"><?= e($errors['password']) ?></small><?php endif; ?>
                </div>
                <button class="btn btn-primary w-100">Zaloguj się</button>
            </form>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
