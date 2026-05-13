<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';
require_admin();

$userRepository = new UserRepository(pdo());
$friendshipRepository = new FriendshipRepository(pdo());
$gymRepository = new GymRepository(pdo());
$exerciseRepository = new ExerciseRepository(pdo());
$eventRepository = new EventRepository(pdo());
$planRepository = new TrainingPlanRepository(pdo());
$logRepository = new WorkoutLogRepository(mongo_db());
$progressRepository = new ProgressRepository(mongo_db());
$commentRepository = new CommentRepository(mongo_db());
$notificationRepository = new NotificationRepository(mongo_db());
$activityRepository = new ActivityLogRepository(mongo_db());

$stats = [
    'users' => $userRepository->countAll(),
    'friendships' => $friendshipRepository->countAll(),
    'gyms' => $gymRepository->countAll(),
    'exercises' => $exerciseRepository->countAll(),
    'events' => $eventRepository->countAll(),
    'plans' => $planRepository->countAll(),
    'logs' => $logRepository->countAll(),
    'progress' => $progressRepository->countAll(),
    'comments' => $commentRepository->countAll(),
    'notifications' => $notificationRepository->countAll(),
];

$recentUsers = $userRepository->latest(5);
$pendingFriendships = $friendshipRepository->pending(5);
$recentEvents = array_slice($eventRepository->allAdmin(), 0, 5);
$recentLogs = $logRepository->all(5);
$recentComments = $commentRepository->all(5);
$recentNotifications = $notificationRepository->all(5);
$recentActivity = $activityRepository->all(8);

$pageTitle = 'Admin Dashboard';
require_once base_path('app/includes/header.php');
?>
<?php if (!mongo_available()): ?>
    <div class="alert alert-warning">
        MongoDB nie jest teraz dostepne. W panelu admina zobaczysz dane relacyjne, ale logi, progres i dokumenty Mongo beda chwilowo puste.
    </div>
<?php endif; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <p class="section-kicker mb-2">Panel administratora</p>
        <h1 class="h3 mb-1">Operacyjny widok systemu GymBRO</h1>
        <p class="muted mb-0">Szybki przeglad kluczowych liczb, najnowszych rekordow i wejsc do zarzadzania.</p>
    </div>
    <a href="<?= e(url('/dashboard.php')) ?>" class="btn btn-outline-primary">Powrot do aplikacji</a>
</div>

<div class="row g-3 mb-4">
    <?php foreach ([
        ['Uzytkownicy', $stats['users']],
        ['Znajomosci', $stats['friendships']],
        ['Silownie', $stats['gyms']],
        ['Cwiczenia', $stats['exercises']],
        ['Wydarzenia', $stats['events']],
        ['Plany', $stats['plans']],
        ['Logi', $stats['logs']],
        ['Pomiary', $stats['progress']],
        ['Komentarze', $stats['comments']],
        ['Powiadomienia', $stats['notifications']],
    ] as [$label, $value]): ?>
        <div class="col-sm-6 col-xl">
            <div class="mini-stat h-100">
                <div class="muted small"><?= e($label) ?></div>
                <div class="fw-bold fs-4"><?= e((string) $value) ?></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card p-4 mb-4">
            <div class="panel-title">
                <div>
                    <h2 class="h4 mb-1">Szybkie wejscia</h2>
                    <div class="meta-line">Najwazniejsze sekcje zarzadzania w jednym miejscu.</div>
                </div>
            </div>
            <div class="quick-grid">
                <a class="quick-link" href="<?= e(url('/admin/users/index.php')) ?>"><div class="fw-semibold mb-1">Uzytkownicy</div><div class="meta-line">Edycja danych i usuwanie kont.</div></a>
                <a class="quick-link" href="<?= e(url('/admin/events/index.php')) ?>"><div class="fw-semibold mb-1">Wydarzenia</div><div class="meta-line">Pelny wglad i CRUD dla treningow.</div></a>
                <a class="quick-link" href="<?= e(url('/admin/plans/index.php')) ?>"><div class="fw-semibold mb-1">Plany</div><div class="meta-line">Zarzadzanie planami, dniami i cwiczeniami.</div></a>
                <a class="quick-link" href="<?= e(url('/admin/logs/index.php')) ?>"><div class="fw-semibold mb-1">Logi treningowe</div><div class="meta-line">Wpisy z Mongo wraz z seriami.</div></a>
                <a class="quick-link" href="<?= e(url('/admin/progress/index.php')) ?>"><div class="fw-semibold mb-1">Progres</div><div class="meta-line">Pomiary, typy i wartosci.</div></a>
                <a class="quick-link" href="<?= e(url('/admin/notifications/index.php')) ?>"><div class="fw-semibold mb-1">Powiadomienia</div><div class="meta-line">Wiadomosci systemowe i odczyt.</div></a>
            </div>
        </div>

        <div class="card p-4 mb-4">
            <div class="panel-title">
                <div>
                    <h2 class="h4 mb-1">Ostatnia aktywnosc systemowa</h2>
                    <div class="meta-line">Najswiezsze wpisy activity log z calej aplikacji.</div>
                </div>
                <a href="<?= e(url('/admin/activity/index.php')) ?>" class="btn btn-outline-primary btn-sm">Cala lista</a>
            </div>
            <?php if (!$recentActivity): ?>
                <div class="empty-state">Brak wpisow aktywnosci.</div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($recentActivity as $activity): ?>
                        <div class="list-group-item px-0 py-3">
                            <div class="fw-semibold">User #<?= e((string) admin_value($activity, 'user_id')) ?>: <?= e(activity_label((string) admin_value($activity, 'action'), admin_value($activity, 'details', []))) ?></div>
                            <div class="muted small"><?= e((string) admin_value($activity, 'created_at')) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card p-4 h-100">
                    <div class="panel-title">
                        <div>
                            <h2 class="h4 mb-1">Najnowsze logi Mongo</h2>
                            <div class="meta-line">Szybki podglad workout logs.</div>
                        </div>
                        <a href="<?= e(url('/admin/logs/index.php')) ?>" class="btn btn-outline-primary btn-sm">Logi</a>
                    </div>
                    <?php if (!$recentLogs): ?>
                        <div class="empty-state">Brak logow albo MongoDB jest niedostepne.</div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentLogs as $log): ?>
                                <div class="list-group-item px-0 py-3">
                                    <div class="fw-semibold">User #<?= e((string) admin_value($log, 'user_id')) ?> • <?= e((string) admin_value($log, 'training_date')) ?></div>
                                    <div class="muted small"><?= e((string) admin_value($log, 'duration_minutes')) ?> min • <?= e((string) admin_value($log, 'mood')) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card p-4 h-100">
                    <div class="panel-title">
                        <div>
                            <h2 class="h4 mb-1">Najnowsze komentarze</h2>
                            <div class="meta-line">Ostatnie wpisy do planow treningowych.</div>
                        </div>
                        <a href="<?= e(url('/admin/comments/index.php')) ?>" class="btn btn-outline-primary btn-sm">Komentarze</a>
                    </div>
                    <?php if (!$recentComments): ?>
                        <div class="empty-state">Brak komentarzy albo MongoDB jest niedostepne.</div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentComments as $comment): ?>
                                <div class="list-group-item px-0 py-3">
                                    <div class="fw-semibold">Plan #<?= e((string) admin_value($comment, 'plan_id')) ?> • <?= e((string) admin_value($comment, 'user_name')) ?></div>
                                    <div class="small"><?= e((string) admin_value($comment, 'content')) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card p-4 mb-4">
            <div class="panel-title">
                <div>
                    <h2 class="h4 mb-1">Ostrzezenia i pending</h2>
                    <div class="meta-line">Szybkie punkty do sprawdzenia przez admina.</div>
                </div>
            </div>
            <?php if (!$pendingFriendships && !$recentEvents): ?>
                <div class="empty-state">Brak oczekujacych spraw.</div>
            <?php else: ?>
                <div class="stack-list">
                    <?php foreach ($pendingFriendships as $pending): ?>
                        <div class="mini-stat">
                            <div class="fw-semibold">Zaproszenie czeka na decyzje</div>
                            <div class="meta-line"><?= e($pending['requester_name']) ?> → <?= e($pending['receiver_name']) ?></div>
                        </div>
                    <?php endforeach; ?>
                    <?php foreach (array_slice($recentEvents, 0, 3) as $event): ?>
                        <div class="mini-stat">
                            <div class="fw-semibold"><?= e($event['title']) ?></div>
                            <div class="meta-line"><?= e($event['event_date']) ?> • <?= e($event['status']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="card p-4 mb-4">
            <div class="panel-title">
                <div>
                    <h2 class="h4 mb-1">Najnowsze rejestracje</h2>
                    <div class="meta-line">Konta dodane najpozniej.</div>
                </div>
                <a href="<?= e(url('/admin/users/index.php')) ?>" class="btn btn-outline-primary btn-sm">Uzytkownicy</a>
            </div>
            <?php if (!$recentUsers): ?>
                <div class="empty-state">Brak kont.</div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($recentUsers as $user): ?>
                        <div class="list-group-item px-0 py-3">
                            <div class="fw-semibold"><?= e($user['name']) ?></div>
                            <div class="muted small"><?= e($user['email']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="card p-4">
            <div class="panel-title">
                <div>
                    <h2 class="h4 mb-1">Ostatnie powiadomienia</h2>
                    <div class="meta-line">Wglad w strumien notifications.</div>
                </div>
                <a href="<?= e(url('/admin/notifications/index.php')) ?>" class="btn btn-outline-primary btn-sm">Powiadomienia</a>
            </div>
            <?php if (!$recentNotifications): ?>
                <div class="empty-state">Brak powiadomien albo MongoDB jest niedostepne.</div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($recentNotifications as $notification): ?>
                        <div class="list-group-item px-0 py-3">
                            <div class="fw-semibold"><?= e((string) admin_value($notification, 'title')) ?></div>
                            <div class="muted small">User #<?= e((string) admin_value($notification, 'user_id')) ?> • <?= e((string) admin_value($notification, 'created_at')) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
