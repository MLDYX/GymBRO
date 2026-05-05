<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();

$repository = new FriendshipRepository(pdo());
$userRepository = new UserRepository(pdo());
$friends = $repository->getFriends(current_user_id());
$incoming = $repository->getIncomingRequests(current_user_id());
$outgoing = $repository->getOutgoingRequests(current_user_id());
$discover = array_values(array_filter(
    $userRepository->allExcept(current_user_id()),
    static fn(array $user): bool => !$repository->areFriendsOrPending(current_user_id(), (int) $user['id'])
));

$pageTitle = 'Znajomi';
require_once base_path('app/includes/header.php');
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <p class="section-kicker mb-2">Twoja paczka</p>
        <h1 class="h3 mb-1">Znajomi i zaproszenia</h1>
        <p class="muted mb-0">Tu ogarniasz ekipę, wspólne kontakty i nowe osoby do dodania.</p>
    </div>
    <a href="/events/index.php" class="btn btn-outline-primary">Zobacz spotkania</a>
</div>

<div class="row g-4">
    <div class="col-xl-5">
        <div class="card p-4 h-100">
            <div class="panel-title">
                <div>
                    <h2 class="h4 mb-1">Twoi znajomi</h2>
                    <div class="meta-line"><?= count($friends) ?> osób w Twojej paczce</div>
                </div>
            </div>
            <?php if (!$friends): ?>
                <div class="empty-state">Nie masz jeszcze znajomych. Dodaj kogoś z sekcji obok i zacznij ustawiać wspólne treningi.</div>
            <?php else: ?>
                <div class="stack-list">
                    <?php foreach ($friends as $friend): ?>
                        <div class="friend-card">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="<?= e(avatar_url($friend['avatar_path'] ?? null)) ?>" alt="Avatar" class="rounded-circle border" style="width: 52px; height: 52px; object-fit: cover;">
                                    <div>
                                    <div class="fw-semibold"><?= e($friend['name']) ?></div>
                                    <div class="meta-line"><?= e($friend['training_level'] ?? 'Ćwiczy regularnie') ?> • <?= e($friend['goal'] ?? 'bez ustawionego celu') ?></div>
                                    </div>
                                </div>
                                <a href="/users/show.php?id=<?= e((string) $friend['user_id']) ?>" class="btn btn-outline-primary btn-sm">Profil</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card p-4 h-100">
            <div class="panel-title">
                <div>
                    <h2 class="h4 mb-1">Zaproszenia</h2>
                    <div class="meta-line">Najpierw to, co wymaga Twojej decyzji.</div>
                </div>
            </div>
            <?php if (!$incoming): ?>
                <div class="empty-state mb-3">Nie masz nowych zaproszeń.</div>
            <?php else: ?>
                <?php foreach ($incoming as $request): ?>
                    <div class="border rounded-4 p-3 mb-3">
                        <div class="fw-semibold"><?= e($request['name']) ?></div>
                        <div class="muted small mb-3"><?= e($request['email']) ?></div>
                        <div class="d-flex gap-2">
                            <form method="post" action="/friendships/accept.php?id=<?= e((string) $request['id']) ?>">
                                <?= csrf_field() ?>
                                <button class="btn btn-primary btn-sm">Akceptuj</button>
                            </form>
                            <form method="post" action="/friendships/reject.php?id=<?= e((string) $request['id']) ?>">
                                <?= csrf_field() ?>
                                <button class="btn btn-outline-secondary btn-sm">Odrzuć</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="border-top pt-4 mt-4">
                <h3 class="h5 mb-3">Wysłane</h3>
                <?php if (!$outgoing): ?>
                    <div class="empty-state">Nie czeka żadne wysłane zaproszenie.</div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($outgoing as $request): ?>
                            <div class="list-group-item px-0 py-3">
                                <div class="fw-semibold"><?= e($request['name']) ?></div>
                                <div class="muted small">Status: czeka na odpowiedź</div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-xl-3">
        <div class="card p-4 h-100">
            <div class="panel-title">
                <div>
                    <h2 class="h4 mb-1">Kogo dodać</h2>
                    <div class="meta-line">Krótka lista osób, które możesz od razu zaprosić.</div>
                </div>
            </div>
            <?php if (!$discover): ?>
                <div class="empty-state">Wygląda na to, że znasz już wszystkich w tej aplikacji.</div>
            <?php else: ?>
                <div class="stack-list">
                    <?php foreach (array_slice($discover, 0, 5) as $user): ?>
                        <div class="friend-card">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <img src="<?= e(avatar_url($user['avatar_path'] ?? null)) ?>" alt="Avatar" class="rounded-circle border" style="width: 52px; height: 52px; object-fit: cover;">
                                <div>
                                    <div class="fw-semibold"><?= e($user['name']) ?></div>
                                    <div class="meta-line"><?= e($user['goal'] ?? 'Trenuje ze znajomymi') ?></div>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <a class="btn btn-outline-primary btn-sm" href="/users/show.php?id=<?= e((string) $user['id']) ?>">Profil</a>
                                <form method="post" action="/friendships/add.php?id=<?= e((string) $user['id']) ?>">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-primary btn-sm">Dodaj</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
