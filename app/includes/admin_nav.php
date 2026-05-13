<?php

declare(strict_types=1);
?>
<div class="context-tabs mb-4 admin-context-tabs">
    <?php foreach (admin_navigation_links() as [$path, $label]): ?>
        <a class="<?= nav_active([$path]) ?>" href="<?= e(url($path)) ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
</div>
