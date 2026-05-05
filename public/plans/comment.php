<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();

verify_csrf();

$planId = (int) ($_GET['id'] ?? 0);
$content = trim((string) ($_POST['content'] ?? ''));
$rating = (int) ($_POST['rating'] ?? 5);

if ($content === '') {
    redirect_with_flash('/plans/show.php?id=' . $planId, 'warning', 'Komentarz nie może być pusty.');
}

$commentRepository = new CommentRepository(mongo_db());
$activityRepository = new ActivityLogRepository(mongo_db());
$commentRepository->addPlanComment($planId, current_user_id(), (string) current_user_name(), $content, $rating);
$activityRepository->create(current_user_id(), 'commented_plan', ['plan_id' => $planId, 'rating' => $rating]);

redirect_with_flash('/plans/show.php?id=' . $planId, 'success', 'Komentarz został dodany.');
