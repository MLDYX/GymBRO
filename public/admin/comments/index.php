<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/bootstrap.php';
require_admin();

$repository = new CommentRepository(mongo_db());
$comments = $repository->all();

$pageTitle = 'Admin • Komentarze';
require_once base_path('app/includes/header.php');
?>
<?php if (!mongo_available()): ?>
    <div class="alert alert-warning">MongoDB nie jest teraz dostepne. Komentarze sa chwilowo niedostepne.</div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="section-kicker mb-2">Admin</p>
        <h1 class="h3 mb-1">Komentarze do planow</h1>
        <p class="muted mb-0">Pelny CRUD dokumentow komentarzy.</p>
    </div>
    <a href="<?= e(url('/admin/comments/create.php')) ?>" class="btn btn-primary">Dodaj komentarz</a>
</div>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>ID</th><th>Plan</th><th>User</th><th>Ocena</th><th>Created at</th><th>Akcje</th></tr></thead>
            <tbody>
            <?php foreach ($comments as $comment): ?>
                <tr>
                    <td><?= e(mongo_id_string($comment) ?? '-') ?></td>
                    <td>#<?= e((string) admin_value($comment, 'plan_id')) ?></td>
                    <td><?= e((string) admin_value($comment, 'user_name')) ?></td>
                    <td><?= e((string) admin_value($comment, 'rating')) ?>/5</td>
                    <td><?= e((string) admin_value($comment, 'created_at')) ?></td>
                    <td class="d-flex gap-2">
                        <a class="btn btn-outline-primary btn-sm" href="<?= e(url('/admin/comments/edit.php?id=' . (string) mongo_id_string($comment))) ?>">Edytuj</a>
                        <form method="post" action="<?= e(url('/admin/comments/delete.php?id=' . (string) mongo_id_string($comment))) ?>">
                            <?= csrf_field() ?>
                            <button class="btn btn-outline-danger btn-sm">Usun</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>
