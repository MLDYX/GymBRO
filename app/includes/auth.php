<?php

declare(strict_types=1);

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        redirect_with_flash('/login.php', 'warning', 'Zaloguj sie, aby przejsc dalej.');
    }

    require_onboarding_if_needed();
}

function require_guest(): void
{
    if (is_logged_in()) {
        redirect('/dashboard.php');
    }
}

function login_user(int $userId, string $userName): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_name'] = $userName;
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function require_onboarding_if_needed(): void
{
    $path = request_path();
    $allowed = [
        '/onboarding.php',
        '/friendships/add.php',
        '/logout.php',
    ];

    if (in_array($path, $allowed, true)) {
        return;
    }

    $profileRepository = new ProfileRepository(pdo());
    $profile = $profileRepository->findByUserId((int) $_SESSION['user_id']);

    if ($profile && !($profile['onboarding_completed'] ?? false)) {
        redirect('/onboarding.php?step=' . onboarding_step_for_profile($profile));
    }
}

function onboarding_step_for_profile(?array $profile): int
{
    if (!$profile) {
        return 1;
    }

    $requiredStepOneFields = [
        'height_cm',
        'weight_kg',
        'training_level',
        'training_experience',
        'goal',
    ];

    foreach ($requiredStepOneFields as $field) {
        $value = $profile[$field] ?? null;
        if ($value === null || trim((string) $value) === '') {
            return 1;
        }
    }

    return 3;
}
