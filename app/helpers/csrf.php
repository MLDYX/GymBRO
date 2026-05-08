<?php

declare(strict_types=1);

function csrf_request_wants_json(): bool
{
    $requestedWith = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));
    $accept = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));

    return $requestedWith === 'xmlhttprequest' || str_contains($accept, 'application/json');
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['_csrf_token'] ?? '';
    if (!hash_equals(csrf_token(), (string) $token)) {
        http_response_code(419);
        if (csrf_request_wants_json()) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'success' => false,
                'message' => 'Sesja formularza wygasla. Odswiez strone i sprobuj ponownie.',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        $fallback = $_SERVER['HTTP_REFERER'] ?? '/dashboard.php';
        redirect_with_flash($fallback, 'warning', 'Sesja formularza wygasla. Odswiez strone i sprobuj ponownie.');
    }
}
