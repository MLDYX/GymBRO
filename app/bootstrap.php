<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

function request_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    return is_string($path) ? $path : '/';
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

function mongo_id_string(object|array $document): ?string
{
    $id = is_array($document) ? ($document['_id'] ?? null) : ($document->_id ?? null);
    return $id ? (string) $id : null;
}
