<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';
require_login();

$planRepository = new TrainingPlanRepository(pdo());
$friendshipRepository = new FriendshipRepository(pdo());
$eventRepository = new EventRepository(pdo());
$workoutLogRepository = new WorkoutLogRepository(mongo_db());
$progressRepository = new ProgressRepository(mongo_db());
$activityRepository = new ActivityLogRepository(mongo_db());
$notificationRepository = new NotificationRepository(mongo_db());
$userRepository = new UserRepository(pdo());

$userId = current_user_id();
$friendIds = $friendshipRepository->friendUserIds($userId);
$friendActivity = $activityRepository->latestForUsers($friendIds, 5);
$friendSuggestions = array_values(array_filter(
    $userRepository->allExcept($userId),
    static fn(array $user): bool => !$friendshipRepository->areFriendsOrPending($userId, (int) $user['id'])
));

$progressType = 'body_weight';
$chartData = $progressRepository->chartData($userId, $progressType);
if (!$chartData['labels']) {
    $latestProgress = $progressRepository->latestByUser($userId, 10);
    if ($latestProgress) {
        $progressType = $latestProgress[0]->type;
        $chartData = $progressRepository->chartData($userId, $progressType);
    }
}

$stats = [
    'logs' => $workoutLogRepository->countByUser($userId),
    'plans' => $planRepository->countByUser($userId),
    'events' => $eventRepository->countUpcomingForUser($userId),
    'friends' => $friendshipRepository->countFriends($userId),
];

$activities = $activityRepository->latest($userId, 5);
$notifications = $notificationRepository->latest($userId, 5);
$nearestEvent = $eventRepository->nearestForUser($userId);
$latestMeasurements = $progressRepository->latestByUser($userId, 5);

$pageTitle = 'Dashboard';
require_once base_path('app/includes/header.php');
?>
<?php if (!mongo_available()): ?>
    <div class="alert alert-warning">
        MongoDB nie jest teraz dostępne. Progres, aktywność i powiadomienia będą chwilowo puste, ale reszta aplikacji dalej działa.
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="soft-card p-4 p-lg-5 mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                <div>
                    <p class="section-kicker mb-2">Twoje centrum działań</p>
                    <h1 class="display-6 fw-bold mb-2">Cześć, <?= e(current_user_name()) ?>.</h1>
                    <p class="muted mb-0">Najpierw treningi i znajomi, a dopiero potem cała reszta.</p>
                </div>
                <a href="/events/create.php" class="btn btn-primary">Umów trening</a>
            </div>
            <div class="quick-grid">
                <a class="quick-link" href="/logs/create.php">
                    <div class="fw-semibold mb-1">Dodaj trening</div>
                    <div class="meta-line">Zapisz wykonany trening i serie.</div>
                </a>
                <a class="quick-link" href="/events/create.php">
                    <div class="fw-semibold mb-1">Umów spotkanie</div>
                    <div class="meta-line">Wybierz siłownię, godzinę i ekipę.</div>
                </a>
                <a class="quick-link" href="/friendships/index.php">
                    <div class="fw-semibold mb-1">Otwórz znajomych</div>
                    <div class="meta-line">Sprawdź zaproszenia i dodaj nowe osoby.</div>
                </a>
            </div>
        </div>

        <div class="card p-4 mb-4">
            <div class="panel-title">
                <div>
                    <h2 class="h4 mb-1">Najbliższe wspólne spotkanie</h2>
                    <div class="meta-line">Najważniejszy punkt przed treningiem.</div>
                </div>
                <a href="/events/index.php" class="btn btn-outline-primary btn-sm">Wszystkie spotkania</a>
            </div>
            <?php if ($nearestEvent): ?>
                <div class="event-card">
                    <div class="d-flex flex-wrap justify-content-between gap-3 mb-2">
                        <div>
                            <div class="fw-semibold fs-5"><?= e($nearestEvent['title']) ?></div>
                            <div class="meta-line"><?= e($nearestEvent['event_date']) ?> • <?= e($nearestEvent['start_time']) ?></div>
                        </div>
                        <a href="/events/show.php?id=<?= e((string) $nearestEvent['id']) ?>" class="btn btn-primary btn-sm">Szczegóły</a>
                    </div>
                    <div class="meta-line"><?= e($nearestEvent['gym_name'] ?? 'Bez wybranej siłowni') ?></div>
                </div>
            <?php else: ?>
                <div class="empty-state">Nie masz jeszcze ustawionego wspólnego treningu.</div>
            <?php endif; ?>
        </div>

        <div class="card p-4">
            <div class="panel-title">
                <div>
                    <h2 class="h4 mb-1">Co robi paczka</h2>
                    <div class="meta-line">Szybki wgląd w aktywność znajomych.</div>
                </div>
                <a href="/friendships/index.php" class="btn btn-outline-primary btn-sm">Znajomi</a>
            </div>
            <?php if (!$friendActivity): ?>
                <div class="empty-state">Gdy znajomi dodadzą trening, plan lub spotkanie, zobaczysz to tutaj.</div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($friendActivity as $activity): ?>
                        <div class="list-group-item px-0 py-3">
                            <div class="fw-semibold"><?= e(activity_label((string) $activity->action, (array) $activity->details)) ?></div>
                            <div class="muted small"><?= e((string) $activity->created_at) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card p-4 mb-4">
            <div class="panel-title">
                <div>
                    <h2 class="h4 mb-1">Twój progres</h2>
                    <div class="meta-line">Krótki skrót bez wchodzenia głębiej.</div>
                </div>
                <a href="/progress/index.php" class="btn btn-outline-primary btn-sm">Cały progres</a>
            </div>
            <div class="chart-wrapper">
                <canvas
                    data-chart
                    data-label="<?= e(progress_type_label($progressType)) ?>"
                    data-labels='<?= e(json_encode($chartData['labels'], JSON_UNESCAPED_UNICODE)) ?>'
                    data-values='<?= e(json_encode($chartData['values'])) ?>'></canvas>
            </div>
        </div>

        <div class="card p-4 mb-4">
            <div class="panel-title">
                <div>
                    <h2 class="h4 mb-1">Szybki stan</h2>
                    <div class="meta-line">Najważniejsze liczby bez przeładowania.</div>
                </div>
                <a href="/plans/index.php" class="btn btn-outline-primary btn-sm">Plany</a>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-6"><div class="mini-stat"><div class="muted small">Plany</div><div class="fw-bold fs-4"><?= e((string) $stats['plans']) ?></div></div></div>
                <div class="col-6"><div class="mini-stat"><div class="muted small">Znajomi</div><div class="fw-bold fs-4"><?= e((string) $stats['friends']) ?></div></div></div>
                <div class="col-6"><div class="mini-stat"><div class="muted small">Treningi</div><div class="fw-bold fs-4"><?= e((string) $stats['logs']) ?></div></div></div>
                <div class="col-6"><div class="mini-stat"><div class="muted small">Spotkania</div><div class="fw-bold fs-4"><?= e((string) $stats['events']) ?></div></div></div>
            </div>
            <?php if ($friendSuggestions !== []): ?>
                <div class="border-top pt-3">
                    <div class="fw-semibold mb-2">Kogo możesz dodać</div>
                    <?php foreach (array_slice($friendSuggestions, 0, 3) as $suggestion): ?>
                        <div class="d-flex justify-content-between align-items-center py-2">
                            <div>
                                <div class="fw-semibold"><?= e($suggestion['name']) ?></div>
                                <div class="muted small"><?= e($suggestion['goal'] ?? 'Ćwiczy regularnie') ?></div>
                            </div>
                            <form method="post" action="/friendships/add.php?id=<?= e((string) $suggestion['id']) ?>">
                                <?= csrf_field() ?>
                                <button class="btn btn-outline-primary btn-sm">Dodaj</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="card p-4 mb-4">
            <h2 class="h4 mb-3">Twoja aktywność</h2>
            <?php if (!$activities): ?>
                <div class="empty-state">Jeszcze nic tu nie ma. Dodaj trening albo ustaw spotkanie.</div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($activities as $activity): ?>
                        <div class="list-group-item px-0 py-3">
                            <div class="fw-semibold"><?= e(activity_label((string) $activity->action, (array) $activity->details)) ?></div>
                            <div class="muted small"><?= e((string) $activity->created_at) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="card p-4 mb-4">
            <h2 class="h4 mb-3">Ostatnie pomiary</h2>
            <?php if (!$latestMeasurements): ?>
                <div class="empty-state">Dodaj pierwszy pomiar progresu.</div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($latestMeasurements as $measurement): ?>
                        <div class="list-group-item px-0 py-3">
                            <div class="fw-semibold"><?= e(progress_type_label((string) $measurement->type)) ?>: <?= e((string) $measurement->value) ?> <?= e((string) $measurement->unit) ?></div>
                            <div class="muted small"><?= e((string) $measurement->date) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="card p-4">
            <h2 class="h4 mb-3">Powiadomienia</h2>
            <?php if (!$notifications): ?>
                <div class="empty-state">Na razie cisza. Nowe ruchy znajomych pojawią się tutaj.</div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($notifications as $notification): ?>
                        <div class="list-group-item px-0 py-3">
                            <div class="fw-semibold"><?= e((string) $notification->title) ?></div>
                            <div class="muted small"><?= e((string) $notification->content) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
