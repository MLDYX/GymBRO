<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();
verify_csrf();

$receiverId = (int) ($_GET['id'] ?? 0);
$repository = new FriendshipRepository(pdo());
$userRepository = new UserRepository(pdo());
$notificationRepository = new NotificationRepository(mongo_db());
$activityRepository = new ActivityLogRepository(mongo_db());
$profileRepository = new ProfileRepository(pdo());

$profile = $profileRepository->findByUserId(current_user_id());
$onboardingIncomplete = $profile && !($profile['onboarding_completed'] ?? false);
$onboardingRedirect = '/onboarding.php?step=' . onboarding_step_for_profile($profile);
$successRedirect = $onboardingIncomplete ? $onboardingRedirect : '/friendships/index.php';
$fallbackRedirect = $onboardingIncomplete ? $onboardingRedirect : '/users/index.php';
$expectsJson = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
    || str_contains(strtolower($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json');

$jsonResponse = static function (bool $success, string $message, string $redirect, int $statusCode = 200): never {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'redirect' => $redirect,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
};

$receiver = $userRepository->findById($receiverId);
if (!$receiver) {
    if ($expectsJson) {
        $jsonResponse(false, 'Nie znaleziono uzytkownika.', $fallbackRedirect, 404);
    }

    redirect_with_flash($fallbackRedirect, 'danger', 'Nie znaleziono uzytkownika.');
}

if ($repository->sendRequest(current_user_id(), $receiverId)) {
    $notificationRepository->create(
        $receiverId,
        'friend_request',
        'Nowe zaproszenie do znajomych',
        current_user_name() . ' wyslal Ci zaproszenie.'
    );
    $activityRepository->create(
        current_user_id(),
        'sent_friend_request',
        ['receiver_id' => $receiverId, 'receiver_name' => $receiver['name']]
    );

    if ($expectsJson) {
        $jsonResponse(true, 'Zaproszenie zostalo wyslane.', $successRedirect);
    }

    redirect_with_flash($successRedirect, 'success', 'Zaproszenie zostalo wyslane.');
}

if ($expectsJson) {
    $jsonResponse(false, 'Nie mozna wyslac zaproszenia.', $fallbackRedirect, 422);
}

redirect_with_flash($fallbackRedirect, 'warning', 'Nie mozna wyslac zaproszenia.');
