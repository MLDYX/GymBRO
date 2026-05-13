<?php

declare(strict_types=1);

$pageTitle = $pageTitle ?? app_name();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | <?= e(app_name()) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= e(asset_url('/assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body>
<?php require_once __DIR__ . '/navbar.php'; ?>
<main class="py-4">
    <div class="container">
        <?php render_flashes(); ?>
        <?php if (is_admin() && str_starts_with(request_path(), '/admin')): ?>
            <?php require __DIR__ . '/admin_nav.php'; ?>
        <?php endif; ?>
