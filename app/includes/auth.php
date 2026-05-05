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
