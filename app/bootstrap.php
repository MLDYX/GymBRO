<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ob_start(static function (string $buffer): string {
    $basePath = app_base_path();
    if ($basePath === '') {
        return $buffer;
    }

    $basePrefix = ltrim($basePath, '/');

    return preg_replace_callback(
        '/\b(href|src|action)=([\"\'])\/(?!\/)([^\"\']*)\2/',
        static function (array $matches) use ($basePath, $basePrefix): string {
            $target = $matches[3];
            if ($target === $basePrefix || str_starts_with($target, $basePrefix . '/')) {
                return $matches[0];
            }

            return sprintf('%s=%s%s/%s%s', $matches[1], $matches[2], $basePath, $target, $matches[2]);
        },
        $buffer
    ) ?? $buffer;
});

$vendorAutoload = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($vendorAutoload)) {
    require_once $vendorAutoload;
}

spl_autoload_register(static function (string $class): void {
    $repositoryPath = __DIR__ . '/repositories/' . $class . '.php';
    if (file_exists($repositoryPath)) {
        require_once $repositoryPath;
    }
});

require_once __DIR__ . '/helpers/redirect.php';
require_once __DIR__ . '/helpers/validation.php';
require_once __DIR__ . '/helpers/csrf.php';
require_once __DIR__ . '/includes/flash.php';
require_once __DIR__ . '/includes/auth.php';

$GLOBALS['gymbro_pdo'] = require __DIR__ . '/config/postgres.php';
$GLOBALS['gymbro_mongo'] = require __DIR__ . '/config/mongodb.php';

function pdo(): PDO
{
    return $GLOBALS['gymbro_pdo'];
}

function mongo_client(): ?MongoDB\Client
{
    return $GLOBALS['gymbro_mongo']['client'] ?? null;
}

function mongo_db(): ?MongoDB\Database
{
    $database = $GLOBALS['gymbro_mongo']['database'] ?? null;
    return $database instanceof MongoDB\Database ? $database : null;
}

function mongo_available(): bool
{
    return (bool) ($GLOBALS['gymbro_mongo']['available'] ?? false);
}

function mongo_error_message(): ?string
{
    $error = $GLOBALS['gymbro_mongo']['error'] ?? null;
    return is_string($error) ? $error : null;
}

function app_name(): string
{
    return 'GymBRO';
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function app_base_path(): string
{
    static $basePath = null;

    if ($basePath !== null) {
        return $basePath;
    }

    $configured = trim((string) (getenv('APP_BASE_PATH') ?: ''));
    if ($configured === '' || $configured === '/') {
        $basePath = '';
        return $basePath;
    }

    $configured = '/' . trim($configured, '/');
    $basePath = $configured;

    return $basePath;
}

function url(string $path = '/'): string
{
    if ($path === '') {
        $path = '/';
    }

    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }

    if ($path[0] !== '/') {
        $path = '/' . $path;
    }

    $basePath = app_base_path();
    if ($basePath === '') {
        return $path;
    }

    return $basePath . $path;
}

function asset_url(string $path): string
{
    return url($path);
}

function old(string $key, string $default = ''): string
{
    return e($_POST[$key] ?? $default);
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function current_user_id(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function current_user_name(): ?string
{
    return $_SESSION['user_name'] ?? null;
}

function base_path(string $path = ''): string
{
    $root = dirname(__DIR__);
    return $path !== '' ? $root . '/' . ltrim($path, '/') : $root;
}

function public_path(string $path = ''): string
{
    $root = base_path('public');
    return $path !== '' ? $root . '/' . ltrim($path, '/') : $root;
}

function now_string(): string
{
    return date('Y-m-d H:i:s');
}

function php_ini_bytes(string $value): int
{
    $value = trim($value);
    if ($value === '') {
        return 0;
    }

    $number = (int) $value;
    $suffix = strtolower($value[strlen($value) - 1]);

    return match ($suffix) {
        'g' => $number * 1024 * 1024 * 1024,
        'm' => $number * 1024 * 1024,
        'k' => $number * 1024,
        default => (int) $value,
    };
}

function request_content_length(): int
{
    return isset($_SERVER['CONTENT_LENGTH']) ? (int) $_SERVER['CONTENT_LENGTH'] : 0;
}

function request_exceeds_post_limit(): bool
{
    if (!is_post()) {
        return false;
    }

    $contentLength = request_content_length();
    if ($contentLength <= 0) {
        return false;
    }

    $postMaxSize = php_ini_bytes((string) ini_get('post_max_size'));

    return $postMaxSize > 0 && $contentLength > $postMaxSize;
}

function request_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if (!is_string($path)) {
        return '/';
    }

    $basePath = app_base_path();
    if ($basePath !== '' && str_starts_with($path, $basePath)) {
        $path = substr($path, strlen($basePath)) ?: '/';
    }

    return $path;
}

function nav_active(array $paths): string
{
    $currentPath = request_path();
    foreach ($paths as $path) {
        $normalized = rtrim($path, '/');
        if ($currentPath === $path || ($normalized !== '' && str_starts_with($currentPath, $normalized . '/'))) {
            return 'active';
        }
    }

    return '';
}

function progress_type_label(string $type): string
{
    return match ($type) {
        'body_weight' => 'Masa ciała',
        'bench_press' => 'Wyciskanie',
        'squat' => 'Przysiad',
        'deadlift' => 'Martwy ciąg',
        'arm_circumference' => 'Obwód ramienia',
        'chest_circumference' => 'Obwód klatki',
        'waist_circumference' => 'Obwód pasa',
        default => ucfirst(str_replace('_', ' ', $type)),
    };
}

function activity_label(string $action, array|object $details = []): string
{
    $payload = is_object($details) ? (array) $details : $details;

    return match ($action) {
        'registered' => 'Dołączył do GymBRO',
        'created_training_plan' => 'Ułożył plan: ' . ($payload['plan_name'] ?? 'plan treningowy'),
        'created_workout_log' => 'Dodał nowy trening',
        'joined_event' => 'Dołączył do wspólnego treningu',
        'left_event' => 'Opuścił spotkanie treningowe',
        'created_event' => 'Ustawił nowe spotkanie',
        'sent_friend_request' => 'Wysłał zaproszenie do znajomych',
        'accepted_friend_request' => 'Zaakceptował zaproszenie',
        'rejected_friend_request' => 'Odrzucił zaproszenie',
        'commented_plan' => 'Zostawił opinię o planie',
        'added_progress_measurement' => 'Dodał pomiar progresu',
        default => ucfirst(str_replace('_', ' ', $action)),
    };
}

function avatar_url(?string $path): string
{
    return $path && trim($path) !== ''
        ? $path
        : 'https://placehold.co/160x160/e2e8f0/334155?text=GymBRO';
}

function mongo_id_string(object|array $document): ?string
{
    $id = is_array($document) ? ($document['_id'] ?? null) : ($document->_id ?? null);
    return $id ? (string) $id : null;
}
