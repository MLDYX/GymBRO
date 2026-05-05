<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();

$userRepository = new UserRepository(pdo());
$friendshipRepository = new FriendshipRepository(pdo());
$users = $userRepository->allExcept(current_user_id());

$pageTitle = 'Uzytkownicy';
require_once base_path('app/includes/header.php');
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Społeczność GymBRO</h1>
        <p class="muted mb-0">Poznaj innych użytkowników i dodaj ich do znajomych.</p>
    </div>
</div>
<div class="row g-4">
    <?php foreach ($users as $user): ?>
        <div class="col-md-6 col-xl-4">
            <div class="card p-4 h-100">
                <h2 class="h5 mb-1"><?= e($user['name']) ?></h2>
                <p class="muted mb-3"><?= e($user['training_level'] ?? 'Brak poziomu') ?> | <?= e($user['goal'] ?? 'Brak celu') ?></p>
                <p class="small"><?= e($user['bio'] ?? 'Ten użytkownik nie uzupełnił jeszcze bio.') ?></p>
                <div class="mt-auto d-flex gap-2">
                    <a class="btn btn-outline-primary btn-sm" href="/users/show.php?id=<?= e((string) $user['id']) ?>">Zobacz profil</a>
                    <?php if (!$friendshipRepository->areFriendsOrPending(current_user_id(), (int) $user['id'])): ?>
                        <form method="post" action="/friendships/add.php?id=<?= e((string) $user['id']) ?>">
                            <?= csrf_field() ?>
                            <button class="btn btn-primary btn-sm">Dodaj znajomego</button>
                        </form>
                    <?php else: ?>
                        <span class="badge text-bg-light align-self-center">Relacja istnieje</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
