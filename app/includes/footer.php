<?php

declare(strict_types=1);
?>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="<?= e(asset_url('/assets/js/charts.js')) ?>"></script>
<?php if (!empty($pageScripts) && is_array($pageScripts)): ?>
    <?php foreach ($pageScripts as $pageScript): ?>
        <script src="<?= e(asset_url((string) $pageScript)) ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>
