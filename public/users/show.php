<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();

$userId = (int) ($_GET['id'] ?? 0);
$userRepository = new UserRepository(pdo());
$planRepository = new TrainingPlanRepository(pdo());
$eventRepository = new EventRepository(pdo());
$friendshipRepository = new FriendshipRepository(pdo());

$user = $userRepository->findById($userId);
if (!$user) {
    redirect_with_flash('/users/index.php', 'danger', 'Nie znaleziono użytkownika.');
}

$publicPlans = $planRepository->publicPlansByUser($userId);
$sharedEvents = $eventRepository->sharedEvents(current_user_id(), $userId);
$hasRelation = $friendshipRepository->areFriendsOrPending(current_user_id(), $userId);

$pageTitle = 'Profil użytkownika';
require_once base_path('app/includes/header.php');
?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card p-4">
            <?php if (!empty($user['avatar_path'])): ?>
                <img src="<?= e($user['avatar_path']) ?>" alt="Avatar" class="rounded-4 border mb-3" style="width: 120px; height: 120px; object-fit: cover;">
            <?php endif; ?>
            <h1 class="h3 mb-1"><?= e($user['name']) ?></h1>
            <p class="muted"><?= e($user['training_level'] ?? 'Brak poziomu') ?></p>
            <p><strong>Staż:</strong> <?= e($user['training_experience'] ?? 'Nie podano') ?></p>
            <p><strong>Cel:</strong> <?= e($user['goal'] ?? 'Nie określono') ?></p>
            <p><strong>Bio:</strong> <?= e($user['bio'] ?? 'Brak opisu.') ?></p>
            <?php if (!$hasRelation && current_user_id() !== $userId): ?>
                <form method="post" action="/friendships/add.php?id=<?= e((string) $userId) ?>">
                    <?= csrf_field() ?>
                    <button class="btn btn-primary">Dodaj znajomego</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card p-4 mb-4">
            <h2 class="h4 mb-3">Publiczne plany</h2>
            <?php if (!$publicPlans): ?>
                <div class="empty-state">Użytkownik nie udostępnił jeszcze żadnych planów.</div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($publicPlans as $plan): ?>
                        <div class="list-group-item px-0 py-3 d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold"><?= e($plan['name']) ?></div>
                                <div class="muted small"><?= e($plan['goal'] ?? 'Brak celu') ?></div>
                            </div>
                            <a class="btn btn-outline-primary btn-sm" href="/plans/show.php?id=<?= e((string) $plan['id']) ?>">Zobacz</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="card p-4">
            <h2 class="h4 mb-3">Wspólne wydarzenia</h2>
            <?php if (!$sharedEvents): ?>
                <div class="empty-state">Brak wspólnych wydarzeń.</div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($sharedEvents as $event): ?>
                        <div class="list-group-item px-0 py-3">
                            <div class="fw-semibold"><?= e($event['title']) ?></div>
                            <div class="muted small"><?= e($event['event_date']) ?> | <?= e($event['start_time']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
